<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SFTPGoService
{
    /**
     * Get the JWT access token from SFTPGo.
     * Caches the token for 15 minutes to optimize performance.
     */
    protected static function getToken()
    {
        $apiUrl = env('SFTPGO_API_URL');
        $username = env('SFTPGO_ADMIN_USERNAME');
        $password = env('SFTPGO_ADMIN_PASSWORD');

        if (empty($apiUrl) || empty($username) || empty($password)) {
            return null;
        }

        $apiUrl = rtrim($apiUrl, '/');

        try {
            return cache()->remember('sftpgo_api_token', 900, function () use ($apiUrl, $username, $password) {
                Log::info("SFTPGo API: Requesting new JWT token.");
                $response = Http::withBasicAuth($username, $password)
                    ->get($apiUrl . '/api/v2/token');

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error("SFTPGo API: Failed to fetch JWT token. Status: " . $response->status() . " Body: " . $response->body());
                return null;
            });
        } catch (\Exception $e) {
            Log::error("SFTPGo API: Exception fetching token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a configured HTTP client for the SFTPGo REST API.
     * Returns null if the required configuration is missing.
     */
    protected static function getClient()
    {
        $apiUrl = env('SFTPGO_API_URL');
        if (empty($apiUrl)) {
            Log::info("SFTPGo API: Sync skipped because SFTPGO_API_URL is not configured yet in the .env file.");
            return null;
        }

        $token = self::getToken();
        if (empty($token)) {
            Log::warning("SFTPGo API: Sync skipped because API token could not be obtained.");
            return null;
        }

        $apiUrl = rtrim($apiUrl, '/');

        return Http::baseUrl($apiUrl . '/api/v2')
            ->withToken($token)
            ->acceptJson();
    }

    /**
     * Synchronize a user model's state to SFTPGo.
     * Handles creation, updates, and deletion (when downgraded or inactive).
     */
    public static function syncUser(User $user, $plainPassword = null)
    {
        $client = self::getClient();
        if (!$client) {
            return; // Config not set, skip silently
        }

        // Determine if they should have SFTP access
        $hasSftpAccess = in_array($user->role, ['trusted', 'admin', 'superadmin']) && $user->is_active;

        if (!$hasSftpAccess) {
            // Delete user if they exist in SFTPGo
            if ($user->sftp_username) {
                self::deleteUser($user->sftp_username);
            }
            return;
        }

        if (!$user->sftp_username) {
            Log::warning("SFTPGo API: Skip sync for User #{$user->id} ({$user->email}) because sftp_username is not generated.");
            return;
        }

        // Construct home directory path (checks SFTPGO_HOME_DIR_ROOT for container-internal paths, falling back to SYSTEM_SSH_STORAGE_ROOT)
        $sftpRoot = rtrim(env('SFTPGO_HOME_DIR_ROOT', env('SYSTEM_SSH_STORAGE_ROOT', '/home/tiquan')), '/');
        if (in_array($user->role, ['admin', 'superadmin'])) {
            $homeDir = $sftpRoot . '/delivered/' . $user->sftp_username;
        } else {
            $homeDir = $sftpRoot . '/uploads/' . $user->sftp_username;
        }

        // AUTO-CREATE PHYSICAL SFTP HOME DIRECTORY ON SYNC
        try {
            $sftpDisk = \Illuminate\Support\Facades\Storage::disk('sftp_delivery');
            $relativeDir = in_array($user->role, ['admin', 'superadmin']) 
                ? 'delivered/' . $user->sftp_username 
                : 'uploads/' . $user->sftp_username;

            if (!$sftpDisk->exists($relativeDir)) {
                $sftpDisk->makeDirectory($relativeDir);
                $sftpDisk->setVisibility($relativeDir, 'public');
            }
        } catch (\Exception $e) {
            Log::warning("SFTPGo API: Could not auto-create directory on sync for {$user->sftp_username}: " . $e->getMessage());
        }

        // Retrieve decrypted plain-text password from model accessor or passed value
        $password = $plainPassword ?: $user->sftp_password;

        $isAdmin = in_array($user->role, ['admin', 'superadmin']);
        $permissions = $isAdmin ? ['*'] : ['list', 'download', 'upload', 'overwrite', 'create_dirs', 'rename', 'chtimes'];

        $userData = [
            'username' => $user->sftp_username,
            'password' => $password,
            'email' => $user->email,
            'status' => 1,
            'home_dir' => $homeDir,
            'uid' => 1000,
            'gid' => 1000,
            'permissions' => [
                '/' => $permissions
            ],
            'max_sessions' => 0,
            'quota_size' => 0,
            'quota_files' => 0,
        ];

        try {
            // Check if user already exists
            $response = $client->get('/users/' . urlencode($user->sftp_username));

            if ($response->successful()) {
                // Update User
                Log::info("SFTPGo API: Syncing update for user {$user->sftp_username}");
                $existingData = json_decode($response->body()) ?: new \stdClass();
                
                // Merge data and preserve unmanaged attributes (casting $existingData to array shallowly, nested objects remain stdClass)
                $payload = array_merge((array) $existingData, $userData);

                // Strip read-only and system-managed fields that SFTPGo rejects in PUT requests
                $readOnlyFields = [
                    'id',
                    'used_quota_size',
                    'used_quota_files',
                    'last_quota_update',
                    'used_upload_data_transfer',
                    'used_download_data_transfer',
                    'last_login',
                    'created_at',
                    'updated_at'
                ];
                foreach ($readOnlyFields as $field) {
                    unset($payload[$field]);
                }

                // Omit password if it is empty/not updated (SFTPGo keeps existing password)
                if (empty($password)) {
                    unset($payload['password']);
                }

                // Fix SFTPGo TOTP secret unmarshal issue (json_decode decodes empty JSON object {} for secret as array [])
                if (isset($payload['filters']) && is_object($payload['filters'])) {
                    if (isset($payload['filters']->totp_config) && is_object($payload['filters']->totp_config)) {
                        if (isset($payload['filters']->totp_config->secret) && is_array($payload['filters']->totp_config->secret) && empty($payload['filters']->totp_config->secret)) {
                            $payload['filters']->totp_config->secret = (object)[];
                        }
                    }
                }

                $putResponse = $client->put('/users/' . urlencode($user->sftp_username), $payload);
                if (!$putResponse->successful()) {
                    Log::error("SFTPGo API: Failed to update user {$user->sftp_username}. Status: " . $putResponse->status() . " Response: " . $putResponse->body());
                } else {
                    Log::info("SFTPGo API: User {$user->sftp_username} updated successfully.");
                }
            } elseif ($response->status() === 404) {
                // Create User
                Log::info("SFTPGo API: Creating new user {$user->sftp_username}");
                
                // For new users, we must have a password
                if (empty($userData['password'])) {
                    $userData['password'] = \Illuminate\Support\Str::random(12);
                }

                $postResponse = $client->post('/users', $userData);
                if (!$postResponse->successful()) {
                    Log::error("SFTPGo API: Failed to create user {$user->sftp_username}. Status: " . $postResponse->status() . " Response: " . $postResponse->body());
                } else {
                    Log::info("SFTPGo API: User {$user->sftp_username} created successfully.");
                }
            } else {
                Log::error("SFTPGo API: Error checking user {$user->sftp_username}. Status: " . $response->status() . " Response: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("SFTPGo API Exception during sync for {$user->sftp_username}: " . $e->getMessage());
        }
    }

    /**
     * Delete a user from SFTPGo.
     */
    public static function deleteUser($username)
    {
        $client = self::getClient();
        if (!$client) {
            return; // Config not set, skip silently
        }

        try {
            Log::info("SFTPGo API: Deleting user {$username}");
            $response = $client->delete('/users/' . urlencode($username));
            if (!$response->successful() && $response->status() !== 404) {
                Log::error("SFTPGo API: Failed to delete user {$username}. Status: " . $response->status() . " Response: " . $response->body());
            } elseif ($response->status() === 404) {
                Log::info("SFTPGo API: User {$username} did not exist in SFTPGo (already deleted).");
            } else {
                Log::info("SFTPGo API: User {$username} deleted successfully.");
            }
        } catch (\Exception $e) {
            Log::error("SFTPGo API Exception during delete for {$username}: " . $e->getMessage());
        }
    }
}
