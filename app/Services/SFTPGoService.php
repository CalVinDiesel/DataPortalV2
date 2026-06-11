<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SFTPGoService
{
    /**
     * Get a configured HTTP client for the SFTPGo REST API.
     * Returns null if the required configuration is missing.
     */
    protected static function getClient()
    {
        $apiUrl = env('SFTPGO_API_URL');
        $username = env('SFTPGO_ADMIN_USERNAME');
        $password = env('SFTPGO_ADMIN_PASSWORD');

        // Graceful fallback: skip if configuration is not yet defined
        if (empty($apiUrl) || empty($username) || empty($password)) {
            Log::info("SFTPGo API: Sync skipped because API keys are not configured yet in the .env file.");
            return null;
        }

        $apiUrl = rtrim($apiUrl, '/');

        return Http::baseUrl($apiUrl . '/api/v2')
            ->withBasicAuth($username, $password)
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

        // Construct home directory path
        $sftpRoot = rtrim(env('SFTP_DELIVERY_ROOT', '/home/tiquan'), '/');
        if (in_array($user->role, ['admin', 'superadmin'])) {
            $homeDir = $sftpRoot . '/delivered/' . $user->sftp_username;
        } else {
            $homeDir = $sftpRoot . '/uploads/' . $user->sftp_username;
        }

        // Retrieve decrypted plain-text password from model accessor or passed value
        $password = $plainPassword ?: $user->sftp_password;

        $userData = [
            'username' => $user->sftp_username,
            'password' => $password,
            'status' => 1,
            'home_dir' => $homeDir,
            'uid' => 1000,
            'gid' => 1000,
            'permissions' => [
                '/' => ['*']
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
                $existingData = $response->json();
                
                // Merge data and preserve unmanaged attributes
                $payload = array_merge($existingData, $userData);

                // Omit password if it is empty/not updated (SFTPGo keeps existing password)
                if (empty($password)) {
                    unset($payload['password']);
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
