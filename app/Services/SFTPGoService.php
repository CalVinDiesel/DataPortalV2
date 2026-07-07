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
     * Query SFTPGo to get the latest user details.
     * Returns array|null
     */
    public static function getUserFromSFTPGo($username)
    {
        $client = self::getClient();
        if (!$client) {
            return null;
        }

        try {
            $response = $client->get('/users/' . urlencode($username));
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("SFTPGo API: Exception fetching user {$username}: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Fetch the latest user details from SFTPGo and save/update them in the local database.
     */
    public static function syncFromSFTPGo(User $user)
    {
        if (empty($user->sftp_username)) {
            return;
        }

        $sftpUser = self::getUserFromSFTPGo($user->sftp_username);
        if ($sftpUser) {
            $updates = [];
            
            // Sync home_dir (with path normalization)
            if (isset($sftpUser['home_dir'])) {
                $sftpHome = rtrim(str_replace('\\', '/', $sftpUser['home_dir']), '/');
                $localHome = rtrim(str_replace('\\', '/', $user->home_dir ?? ''), '/');
                if ($sftpHome !== $localHome) {
                    $updates['home_dir'] = $sftpUser['home_dir'];
                }
            }

            // Sync quota_size
            if (isset($sftpUser['quota_size'])) {
                $sftpQuota = (int)$sftpUser['quota_size'];
                $localQuota = $user->sftp_quota_size ? (int)$user->sftp_quota_size : null;
                if ($sftpQuota !== $localQuota) {
                    $updates['sftp_quota_size'] = $sftpQuota;
                }
            }

            // Sync status (SFTPGo: 1 = active, 0 = disabled)
            if (isset($sftpUser['status'])) {
                $sftpIsActive = $sftpUser['status'] === 1;
                if ($sftpIsActive !== $user->is_active) {
                    $updates['is_active'] = $sftpIsActive;
                    $updates['status'] = $sftpIsActive ? 'active' : 'pending';
                }
            }
            // Sync email if updated in SFTPGo
            if (isset($sftpUser['email']) && !empty($sftpUser['email']) && $sftpUser['email'] !== $user->email) {
                $updates['email'] = $sftpUser['email'];
            }

            if (!empty($updates)) {
                Log::info("SFTPGo API: Syncing updates from SFTPGo to local database for User #{$user->id}: " . json_encode($updates));
                // Update DB directly to prevent Eloquent event loops, but keep model instance updated
                \Illuminate\Support\Facades\DB::table('portal_users')
                    ->where('id', $user->id)
                    ->update($updates);
                
                foreach ($updates as $key => $val) {
                    $user->setAttribute($key, $val);
                }
            }
        }
    }

    /**
     * Synchronize a user model's state to SFTPGo.
     * Handles creation, updates, and deletion (when downgraded or inactive).
     */
    public static function syncUser(User $user, $plainPassword = null)
    {
        // 1. Sync latest changes from SFTPGo to local DB first (preserves admin modifications)
        try {
            self::syncFromSFTPGo($user);
        } catch (\Exception $e) {
            Log::error("SFTPGo API: Exception during pre-sync: " . $e->getMessage());
        }

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

        // 🚀 FETCH EXISTING SFTPGO USER STATE FIRST (v320)
        $existingData = null;
        try {
            $response = $client->get('/users/' . urlencode($user->sftp_username));
            if ($response->successful()) {
                $existingData = json_decode($response->body());
            }
        } catch (\Exception $e) {
            Log::error("SFTPGo API: Error checking existing user: " . $e->getMessage());
        }

        // Determine home directory dynamically, preferring existing SFTPGo setting, then database, then default
        if ($existingData && !empty($existingData->home_dir)) {
            $homeDir = $existingData->home_dir;
            if ($user->home_dir !== $homeDir) {
                \Illuminate\Support\Facades\DB::table('portal_users')
                    ->where('id', $user->id)
                    ->update(['home_dir' => $homeDir]);
                $user->home_dir = $homeDir;
            }
        } elseif (!empty($user->home_dir)) {
            $homeDir = $user->home_dir;
        } else {
            // Construct default home directory path
            $sftpRoot = rtrim(env('SFTPGO_HOME_DIR_ROOT', env('SYSTEM_SSH_STORAGE_ROOT', '/home/tiquan')), '/');
            if (in_array($user->role, ['admin', 'superadmin'])) {
                $homeDir = $sftpRoot . '/delivered/' . $user->sftp_username;
            } else {
                $homeDir = $sftpRoot . '/uploads/' . $user->sftp_username;
            }
            
            // Save computed path to DB
            \Illuminate\Support\Facades\DB::table('portal_users')
                ->where('id', $user->id)
                ->update(['home_dir' => $homeDir]);
            $user->home_dir = $homeDir;
        }

        // AUTO-CREATE PHYSICAL SFTP HOME DIRECTORY ON SYNC
        try {
            $sftpDisk = \Illuminate\Support\Facades\Storage::disk('sftp_delivery');
            
            // Compute relative directory path for storage check
            $sftpRoot = rtrim(env('SFTPGO_HOME_DIR_ROOT', env('SYSTEM_SSH_STORAGE_ROOT', '/home/tiquan')), '/');
            $relativeDir = in_array($user->role, ['admin', 'superadmin']) 
                ? 'delivered/' . $user->sftp_username 
                : 'uploads/' . $user->sftp_username;
                
            if (str_starts_with($homeDir, $sftpRoot)) {
                $relativeDir = ltrim(substr($homeDir, strlen($sftpRoot)), '/');
            }

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
        $defaultPermissions = $isAdmin ? ['*'] : ['list', 'download', 'upload', 'overwrite', 'create_dirs', 'rename', 'chtimes'];

        $sftpQuotaGb = (float) env('SFTPGO_STORAGE_LIMIT_GB', 5);
        $defaultQuotaBytes = (int) ($sftpQuotaGb * 1024 * 1024 * 1024);

        try {
            if ($existingData) {
                // Update User
                Log::info("SFTPGo API: Syncing update for user {$user->sftp_username}");
                
                // Preserve existing attributes set by the SFTPGo administrator
                $uid = isset($existingData->uid) ? $existingData->uid : 1000;
                $gid = isset($existingData->gid) ? $existingData->gid : 1000;
                $maxSessions = isset($existingData->max_sessions) ? $existingData->max_sessions : 0;
                $quotaSize = isset($existingData->quota_size) && $existingData->quota_size > 0 
                    ? $existingData->quota_size 
                    : (!is_null($user->sftp_quota_size) ? $user->sftp_quota_size : $defaultQuotaBytes);
                $quotaFiles = isset($existingData->quota_files) ? $existingData->quota_files : 0;
                $perms = isset($existingData->permissions) ? (array) $existingData->permissions : ['/' => $defaultPermissions];

                $userData = [
                    'username' => $user->sftp_username,
                    'password' => $password,
                    'email' => $user->email,
                    'status' => $user->is_active ? 1 : 0,
                    'home_dir' => $homeDir,
                    'uid' => $uid,
                    'gid' => $gid,
                    'permissions' => $perms,
                    'max_sessions' => $maxSessions,
                    'quota_size' => $quotaSize,
                    'quota_files' => $quotaFiles,
                ];

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

                // Fix SFTPGo TOTP secret unmarshal issue
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
            } else {
                // Create User
                Log::info("SFTPGo API: Creating new user {$user->sftp_username}");
                
                // For new users, we must have a password
                if (empty($password)) {
                    $password = \Illuminate\Support\Str::random(12);
                }

                $userData = [
                    'username' => $user->sftp_username,
                    'password' => $password,
                    'email' => $user->email,
                    'status' => $user->is_active ? 1 : 0,
                    'home_dir' => $homeDir,
                    'uid' => 1000,
                    'gid' => 1000,
                    'permissions' => [
                        '/' => $defaultPermissions
                    ],
                    'max_sessions' => 0,
                    'quota_size' => !is_null($user->sftp_quota_size) ? $user->sftp_quota_size : $defaultQuotaBytes,
                    'quota_files' => 0,
                ];

                $postResponse = $client->post('/users', $userData);
                if (!$postResponse->successful()) {
                    Log::error("SFTPGo API: Failed to create user {$user->sftp_username}. Status: " . $postResponse->status() . " Response: " . $postResponse->body());
                } else {
                    Log::info("SFTPGo API: User {$user->sftp_username} created successfully.");
                }
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
