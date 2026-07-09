<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use App\Models\DisclaimerAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            \Log::warning("ProjectController@index: No authenticated user found. Session might be missing.");
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please log in to view your projects.'
            ], 401);
        }

        $uploads = ClientUpload::where('created_by_email', $user->email)
            ->where('request_status', '!=', 'user_hidden') // 🚀 USER-HIDE (v211): Don't show projects the user "deleted"
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($u) use ($user) {
                $u->client_sftp_user = $user->sftp_username ?: \Illuminate\Support\Str::slug($user->name);
                
                // 🚀 SPEED BOOST (v271): Auto-convert OneDrive preview links to direct download links
                if ($u->delivery_method === 'onedrive' && $u->delivered_file_path) {
                    $u->delivered_file_path = self::convertToDirectOneDriveUrl($u->delivered_file_path);
                }
                
                return $u;
            });

        return response()->json($uploads);
    }

    public function confirmReceived($id)
    {
        $upload = ClientUpload::where('id', $id)
            ->where('created_by_email', Auth::user()->email)
            ->firstOrFail();

        $upload->update(['request_status' => 'completed']);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        // Find the project (Admins can find any, Users only their own)
        $user = Auth::user();
        $isAdmin = ($user->role === 'admin' || $user->role === 'superadmin');

        $query = ClientUpload::where('id', $id);
        if (!$isAdmin) {
            $query->where('created_by_email', $user->email);
        }
        
        $upload = $query->firstOrFail();

        if ($isAdmin) {
            // 🚀 Cleanup physical files on deletion
            // 1. Delete from SFTP server
            try {
                $creator = \App\Models\User::where('email', $upload->created_by_email)->first();
                if ($creator) {
                    $sftpUser = !empty($creator->sftp_username) ? $creator->sftp_username : \Illuminate\Support\Str::slug($creator->name);
                } else {
                    $sftpUser = \Illuminate\Support\Str::slug(explode('@', $upload->created_by_email)[0]);
                }
                
                $relativeSftpPath = "uploads/{$sftpUser}/{$upload->project_id}";
                \Illuminate\Support\Facades\Storage::disk('sftp_delivery')->deleteDirectory($relativeSftpPath);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to delete project folder from SFTP: " . $e->getMessage());
            }

            // 2. Delete from local nitro disk
            try {
                if (\Illuminate\Support\Facades\Storage::disk('nitro')->exists($upload->project_id)) {
                    \Illuminate\Support\Facades\Storage::disk('nitro')->deleteDirectory($upload->project_id);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to delete project folder from local nitro disk: " . $e->getMessage());
            }

            // 🚀 ADMIN AUTHORITY (v211): Permanent Database Deletion
            $upload->delete();
            return response()->json(['success' => true, 'message' => 'Project permanently deleted from database and filesystems.']);
        } else {
            // 🚀 USER REMOVAL (v211): Only hide from their browser view
            $upload->update(['request_status' => 'user_hidden']);
            return response()->json(['success' => true, 'message' => 'Project removed from your view.']);
        }
    }

    public function storeSftp(Request $request)
    {
        // Enforce role permission: Only trusted users and admins can use SFTP.
        // 🚀 STORAGE QUOTA CHECK (v310)
        if (ClientUpload::hasExceededStorageLimit(Auth::user()->email)) {
            $limitGb = ClientUpload::getStorageLimitBytes(Auth::user()->email) / (1024 * 1024 * 1024);
            return response()->json([
                'success' => false,
                'message' => 'Storage Quota Exceeded. You have already used 100% of your ' . $limitGb . ' GB limit. Please delete old projects to free up space.'
            ], 422);
        }

        $user = Auth::user();
        $role = $user->role;

        if (!in_array($role, ['trusted', 'admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'SFTP upload is only available for trusted users.'], 403);
        }

        // Generate credentials early so the folder paths match the SFTP user home directory
        if (!$user->sftp_username) {
            $rawPassword = \Illuminate\Support\Str::random(12);
            $user->sftp_username = \Illuminate\Support\Str::slug($user->name) . '_' . strtolower(\Illuminate\Support\Str::random(6));
            $user->sftp_password = $rawPassword;
            $user->save();
        }

        $request->validate([
            'projectTitle' => 'required|string',
            'projectID' => 'required|string',
            'projectDescription' => 'nullable|string',
            'category' => 'required|string',
            'outputCategory' => 'required|array',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'cameraConfiguration' => 'nullable|string',
            'cameraModels' => 'nullable|string',
            'imageMetadata' => 'nullable|string',
            'captureDate' => 'nullable|date',
        ]);

        // 🚀 CAMERA STANDARDIZATION (v251): Map 'Standard'/'Multiple' and enforce format
        $camModels = $request->cameraConfiguration; 
        if ($camModels === 'multiple' || $camModels === 'Multiple') {
            $finalCam = 'Multi-Lens';
        } elseif ($camModels === 'single' || $camModels === 'Standard' || empty($camModels)) {
            $finalCam = 'Single-Lens';
        } elseif (!str_starts_with(strtolower($camModels), 'multi-lens') && !str_starts_with(strtolower($camModels), 'single-lens')) {
            // If it's just "Thermal", prefix it
            $finalCam = 'Multi-Lens: ' . $camModels;
        } else {
            $finalCam = $camModels;
        }

        $upload = ClientUpload::create([
            'project_id' => $request->projectID,
            'project_title' => $request->projectTitle,
            'project_description' => $request->projectDescription,
            'upload_type' => ($finalCam === 'Multi-Lens' || str_starts_with($finalCam, 'Multi-Lens')) ? 'sftp_multiple' : 'sftp',
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'camera_models' => $finalCam,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'category' => $request->category,
            'output_categories' => $request->outputCategory,
            'image_metadata' => $request->imageMetadata ?? '[]',
            'capture_date' => $request->captureDate ?? now()->toDateString(),
            'delivery_method' => 'sftp', 
        ]);

        // AUTO-CREATE SFTP DIRECTORIES: One for User Upload, One for Admin Delivery
        try {
            $sftpDisk = Storage::disk('sftp_delivery');
            $user = Auth::user();
            $sftpUser = $user->sftp_username;

            // 1. User Upload Folder (Locker-aware) - 🚀 PATH-SYNC (v163)
            $diskRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
            
            $userBaseDir = 'uploads/' . $sftpUser; 
            if (!$sftpDisk->exists($userBaseDir)) {
                $sftpDisk->makeDirectory($userBaseDir);
                $sftpDisk->setVisibility($userBaseDir, 'public');
            }

            $uploadPathRelative = 'uploads/' . $sftpUser . '/' . $upload->project_id;
            if (!$sftpDisk->exists($uploadPathRelative)) {
                $sftpDisk->makeDirectory($uploadPathRelative);
                $sftpDisk->setVisibility($uploadPathRelative, 'public');
                $sftpDisk->put($uploadPathRelative . '/.ready_for_raw_data', 'Drag your photos into this folder.');
                $sftpDisk->setVisibility($uploadPathRelative . '/.ready_for_raw_data', 'public');
            }
            
            $uploadPathAbsolute = $diskRoot . '/' . $uploadPathRelative;

            // 🚀 SMART-PATH SYNC (v163): 
            $upload->update([
                'file_paths' => [$uploadPathAbsolute],
                'sftp_delivery_path' => $uploadPathAbsolute, // 📂 Path Sync
                'delivered_file_path' => $uploadPathAbsolute, // 📂 Portal View Sync
                'request_status' => 'pending'
            ]);

            // 🚀 SMART-POS DETECTION (v118): Scan for navigation files silently
            try {
                if (str_contains(strtolower($request->imageMetadata), 'pos')) {
                    if (is_dir($uploadPathAbsolute)) {
                        $filesInDir = scandir($uploadPathAbsolute);
                        $posExtensions = ['pos', 'txt', 'csv', 'nav', 'log', 'mrk'];
                        foreach ($filesInDir as $fileName) {
                            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            if (in_array($ext, $posExtensions)) {
                                $upload->update(['drone_pos_file_path' => $uploadPathAbsolute . '/' . $fileName]);
                                break; 
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("  - [SFTP POS SCAN FAIL]: " . $e->getMessage());
            }

            // 2. Admin Delivery Folder (Pre-created for you) - 🚀 PATH-SYNC (v213)
            $deliveryPath = 'uploads/' . $sftpUser . '/' . $upload->project_id . '/delivered';
            if (!$sftpDisk->exists($deliveryPath)) {
                $sftpDisk->makeDirectory($deliveryPath, 0777, true);
            }

            // 🚀 MASTER-FORCE (v123): Use SSH to create folder with 777 immediately
            try {
                $sshPort = (int) env('SYSTEM_SSH_PORT', 22);
                $sshHost = env('SYSTEM_SSH_HOST', config('filesystems.disks.sftp_delivery.host'));
                $sshUser = env('SYSTEM_SSH_USERNAME', 'root');
                $sshPass = env('SYSTEM_SSH_PASSWORD');
                $ssh = new \phpseclib3\Net\SSH2($sshHost, $sshPort);
                if ($ssh->login($sshUser, $sshPass)) {
                    $baseUploadRoot = rtrim(env('SYSTEM_SSH_STORAGE_ROOT', '/srv/sftpgo/data'), '/');
                    $fullPath = $baseUploadRoot . '/' . $uploadPathRelative;
                    $userDir = $baseUploadRoot . '/' . 'uploads/' . $sftpUser;
                    // 🚀 TIMESTAMP WAKEUP (v225): Write and delete a temp file to force filesystem/SFTPGo to update mtime
                    $ssh->exec("mkdir -p " . escapeshellarg($fullPath . "/delivered"));
                    
                    $pathsToWake = [$userDir, $fullPath, $fullPath . "/delivered"];
                    foreach ($pathsToWake as $p) {
                        $tempFile = $p . "/.v" . time();
                        $ssh->exec("touch " . escapeshellarg($tempFile) . " && rm " . escapeshellarg($tempFile));
                    }

                    $ssh->exec("chmod -R 777 " . escapeshellarg($userDir));
                }
            } catch (\Exception $sshEx) {
                \Log::error("SSH Force Failed: " . $sshEx->getMessage());
            }
        } catch (\Exception $e) {
            \Log::warning('Could not auto-create SFTP directories: ' . $e->getMessage());
        }
        // Return connection details for the UI. (Personalized for the user)
        $user = Auth::user();
        $isAdmin = ($user->role === 'admin' || $user->role === 'superadmin');
        $sftpUser = $user->sftp_username;
        
        $sftpDetails = [
            'absolutePath' => rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/') . '/uploads/' . $sftpUser . '/' . $upload->project_id . '/',
            'clientPath'   => '/' . $upload->project_id . '/', 
            'host'         => config('support.sftp_host'),
            'port'         => (int)env('CLIENT_SFTP_PORT', env('SFTP_PORT', 2222)),
        ];

        if ($isAdmin) {
            // Admins see the master server credentials
            $sftpDetails['username'] = config('filesystems.disks.sftp_delivery.username');
            $sftpDetails['password'] = config('filesystems.disks.sftp_delivery.password');
        } else {
            // Ensure they are synced to SFTPGo just in case they were missing
            try {
                \App\Services\SFTPGoService::syncUser($user);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to sync user on storeSftp: " . $e->getMessage());
            }
            $sftpDetails['username'] = $user->sftp_username;
            $sftpDetails['password'] = $user->sftp_password; // 🚀 Show plain text
        }

        return response()->json([
            'success' => true,
            'message' => 'Project registered for SFTP upload.',
            'sftpDetails' => $sftpDetails,
            'project' => $upload
        ]);
    }

    public function storeGoogleDrive(Request $request)
    {
        $gdriveSize = intval($request->googleDriveSize ?? 0);
        // 🚀 STORAGE QUOTA CHECK (v310)
        if (ClientUpload::hasExceededStorageLimit(Auth::user()->email, $gdriveSize)) {
            $limitGb = ClientUpload::getStorageLimitBytes(Auth::user()->email) / (1024 * 1024 * 1024);
            return response()->json([
                'success' => false,
                'message' => 'Storage Quota Exceeded. Registering this project would exceed your ' . $limitGb . ' GB limit. Please delete old projects to free up space.'
            ], 422);
        }

        $request->validate([
            'projectTitle' => 'required|string',
            'projectDescription' => 'required|string',
            'cameraConfiguration' => 'required|string',
            'googleDriveLink' => 'required|url',
            'googleDriveSize' => 'required|integer|min:1',
            'googleDriveCount' => 'required|integer|min:1',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'category' => 'required|string',
            'outputCategory' => 'required|array',
            'imageMetadata' => 'required|string',
            'captureDate' => 'nullable|date',
        ]);

        $link = $request->googleDriveLink;

        // Basic check if it's a google drive link
        if (!str_contains($link, 'drive.google.com')) {
            return response()->json(['success' => false, 'message' => 'Please provide a valid Google Drive link.'], 422);
        }

        // Check if accessible
        try {
            $response = Http::get($link);
            if ($response->failed() || str_contains($response->body(), 'Google Drive - Page Not Found') || str_contains($response->body(), 'Sign in - Google Accounts')) {
                return response()->json(['success' => false, 'message' => 'The Google Drive link is not publicly accessible. Please set it to "Anyone with the link".'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not verify the Google Drive link. Please make sure it is valid and accessible.'], 422);
        }

        $projectId = Str::slug($request->projectTitle) . '-' . Str::random(4);

        // 🚀 CAMERA STANDARDIZATION (v251)
        $camModels = $request->cameraConfiguration;
        if ($camModels === 'multiple' || $camModels === 'Multiple') {
            $finalCam = 'Multi-Lens';
        } elseif ($camModels === 'single' || $camModels === 'Standard' || empty($camModels)) {
            $finalCam = 'Single-Lens';
        } elseif (!str_starts_with(strtolower($camModels), 'multi-lens') && !str_starts_with(strtolower($camModels), 'single-lens')) {
            $finalCam = 'Multi-Lens: ' . $camModels;
        } else {
            $finalCam = $camModels;
        }

        $upload = ClientUpload::create([
            'project_id' => $projectId,
            'project_title' => $request->projectTitle,
            'project_description' => $request->projectDescription,
            'upload_type' => 'google_drive',
            'google_drive_link' => $link,
            'camera_models' => $finalCam,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'category' => $request->category,
            'output_categories' => $request->outputCategory,
            'image_metadata' => $request->imageMetadata,
            'capture_date' => $request->captureDate,
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'delivered_file_path' => $link,
            'total_size_bytes' => $gdriveSize,
            'file_count' => intval($request->googleDriveCount ?? 0),
            'gdrive_delivery_folder_id' => $this->extractGoogleDriveFolderId($link), // 📂 Save ID to DB
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Google Drive project created successfully.',
            'project' => $upload
        ]);
    }

    public function storeOneDrive(Request $request)
    {
        $onedriveSize = intval($request->onedriveSize ?? 0);
        // 🚀 STORAGE QUOTA CHECK (v310)
        if (ClientUpload::hasExceededStorageLimit(Auth::user()->email, $onedriveSize)) {
            $limitGb = ClientUpload::getStorageLimitBytes(Auth::user()->email) / (1024 * 1024 * 1024);
            return response()->json([
                'success' => false,
                'message' => 'Storage Quota Exceeded. Registering this project would exceed your ' . $limitGb . ' GB limit. Please delete old projects to free up space.'
            ], 422);
        }

        $request->validate([
            'projectTitle' => 'required|string',
            'projectDescription' => 'required|string',
            'cameraConfiguration' => 'required|string',
            'onedriveLink' => 'required|url',
            'onedriveItemId' => 'nullable|string',
            'onedriveDriveId' => 'nullable|string',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'category' => 'required|string',
            'outputCategory' => 'required|array',
            'imageMetadata' => 'required|string',
            'captureDate' => 'nullable|date',
        ]);

        $projectId = Str::slug($request->projectTitle) . '-' . Str::random(4);

        // 🚀 CAMERA STANDARDIZATION (v251)
        $camModels = $request->cameraConfiguration;
        if ($camModels === 'multiple' || $camModels === 'Multiple') {
            $finalCam = 'Multi-Lens';
        } elseif ($camModels === 'single' || $camModels === 'Standard' || empty($camModels)) {
            $finalCam = 'Single-Lens';
        } elseif (!str_starts_with(strtolower($camModels), 'multi-lens') && !str_starts_with(strtolower($camModels), 'single-lens')) {
            $finalCam = 'Multi-Lens: ' . $camModels;
        } else {
            $finalCam = $camModels;
        }

        $upload = ClientUpload::create([
            'project_id' => $projectId,
            'project_title' => $request->projectTitle,
            'project_description' => $request->projectDescription,
            'upload_type' => 'onedrive',
            'cloud_provider' => 'onedrive',
            'onedrive_link' => $request->onedriveLink,
            'onedrive_item_id' => $request->onedriveItemId,
            'onedrive_drive_id' => $request->onedriveDriveId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'camera_models' => $finalCam,
            'category' => $request->category,
            'output_categories' => $request->outputCategory,
            'image_metadata' => $request->imageMetadata,
            'capture_date' => $request->captureDate,
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'delivered_file_path' => $request->onedriveLink, // Fallback view path
            'total_size_bytes' => intval($request->onedriveSize ?? 0),
            'file_count' => intval($request->onedriveCount ?? 0),
        ]);

        // 🚀 AUTO-DETECTION (v265): Attempt immediate sync for OneDrive if size is not provided manually
        if (!isset($request->onedriveSize) || $request->onedriveSize <= 0) {
            try {
                // If we don't have IDs yet (manual link), internal sync will try to resolve the link
                $this->syncOneDriveMetadataInternal($upload, $upload->onedrive_item_id, $upload->onedrive_drive_id);
            } catch (\Exception $e) {
                \Log::warning("OneDrive Immediate Sync Failed: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'OneDrive project created successfully.',
            'project' => $upload
        ]);
    }
    public function downloadDelivered($id)
    {
        $upload = ClientUpload::where('id', $id)
            ->where('created_by_email', Auth::user()->email)
            ->firstOrFail();

        if ($upload->request_status !== 'completed' || !$upload->delivered_file_path) {
            return response()->json(['error' => 'File not available yet.'], 404);
        }

        // Check for expiry
        if ($upload->delivered_expires_at && $upload->delivered_expires_at->isPast()) {
            return response()->json(['error' => 'This download link has expired (1 week limit). Please contact admin if you still need the data.'], 410);
        }

        if ($upload->delivery_method === 'portal' || $upload->delivery_method === 'sftp') {
            // 🚀 SESSION UNLOCK (v176): Release the session lock immediately.
            session_write_close();
            ignore_user_abort(true);

            if (function_exists('ini_set')) {
                ini_set('memory_limit', '-1'); 
            }
            set_time_limit(0);

            $filePath = $upload->delivered_file_path;

            // 🚀 LOCAL-FIRST OPTIMIZATION (v176): If both servers are on the same machine, 
            // streaming directly from the disk is INSTANT and bypasses SFTP handshake delays.
            if (file_exists($filePath) && is_readable($filePath)) {
                if (is_dir($filePath)) {
                    // 🚀 DIRECTORY ON-THE-FLY ZIP (v320)
                    $zipName = basename($filePath) . '.zip';
                    $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $zipName);
                    
                    // Create local temporary zip archive path
                    $tempDir = storage_path('app/temp_downloads');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0777, true);
                    }
                    $tempZipPath = $tempDir . '/' . uniqid('dl_') . '.zip';

                    $zip = new \ZipArchive();
                    if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                        $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($filePath),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );

                        foreach ($files as $name => $file) {
                            if (!$file->isDir()) {
                                $realPath = $file->getRealPath();
                                // Build relative path within the zip (stripping the base path)
                                $relativePath = substr($realPath, strlen($filePath) + 1);
                                $zip->addFile($realPath, $relativePath);
                            }
                        }
                        $zip->close();
                    } else {
                        return response()->json(['error' => 'Could not package directory.'], 500);
                    }

                    // Return stream response download and delete temp file after sending
                    return response()->download($tempZipPath, $fileName, [
                        'Content-Type' => 'application/zip',
                        'Cache-Control' => 'no-cache, no-store, must-revalidate',
                        'Pragma' => 'no-cache',
                        'Expires' => '0',
                    ])->deleteFileAfterSend(true);
                } else {
                    // It is a file (could be zip, obj, pdf, fbx, etc.)
                    $originalFileName = basename($filePath);
                    $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);
                    $size = filesize($filePath);
                    
                    // Enforce correct mime-type
                    $mimeType = 'application/octet-stream';
                    try {
                        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
                    } catch (\Exception $e) {}

                    // If it is a zip, force application/zip
                    if (Str::endsWith(strtolower($originalFileName), '.zip')) {
                        $mimeType = 'application/zip';
                    }

                    return response()->stream(function() use ($filePath) {
                        while (ob_get_level() > 0) ob_end_clean();
                        $file = fopen($filePath, 'rb');
                        if ($file) {
                            fpassthru($file);
                            fclose($file);
                        }
                    }, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
                        'Content-Length' => $size,
                        'Cache-Control' => 'no-cache, no-store, must-revalidate',
                        'Pragma' => 'no-cache',
                        'Expires' => '0',
                        'X-Accel-Buffering' => 'no',
                    ]);
                }
            }

            // Fallback to SFTP if local path is not accessible
            $disk = Storage::disk('sftp_delivery');
            
            // 🚀 ROOT STRIPPING (v218): Strip the absolute root prefix to get a relative SFTP path.
            $root = config('filesystems.disks.sftp_delivery.root', '/');
            if (Str::startsWith($filePath, $root)) {
                $filePath = Str::after($filePath, $root);
            }
            $filePath = ltrim($filePath, '/');

            // Check if it is a directory on remote SFTP
            $isDir = false;
            try {
                if (method_exists($disk, 'directoryExists')) {
                    $isDir = $disk->directoryExists($filePath);
                } else {
                    // Fallback to listing contents check
                    $isDir = !empty($disk->listContents($filePath)->toArray());
                }
            } catch (\Exception $e) {}

            if ($isDir) {
                // SFTP DIRECTORY ON-THE-FLY ZIP (v320)
                $zipName = basename($filePath) . '.zip';
                $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $zipName);

                $tempDir = storage_path('app/temp_downloads/' . uniqid('sftp_dl_'));
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                try {
                    // Download all files from SFTP recursively
                    $contents = $disk->listContents($filePath, true);
                    foreach ($contents as $item) {
                        $relativePath = Str::after($item->path(), $filePath . '/');
                        if ($item->isDir()) {
                            $dirToMake = $tempDir . '/' . $relativePath;
                            if (!file_exists($dirToMake)) {
                                mkdir($dirToMake, 0777, true);
                            }
                        } else {
                            $fileLocalPath = $tempDir . '/' . $relativePath;
                            $parentDir = dirname($fileLocalPath);
                            if (!file_exists($parentDir)) {
                                mkdir($parentDir, 0777, true);
                            }
                            $stream = $disk->readStream($item->path());
                            if ($stream) {
                                file_put_contents($fileLocalPath, $stream);
                                fclose($stream);
                            }
                        }
                    }

                    // Zip the downloaded directory
                    $tempZipPath = storage_path('app/temp_downloads/' . uniqid('dl_') . '.zip');
                    $zip = new \ZipArchive();
                    if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                        $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($tempDir),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        foreach ($files as $name => $file) {
                            if (!$file->isDir()) {
                                $realPath = $file->getRealPath();
                                $relativePath = substr($realPath, strlen($tempDir) + 1);
                                $zip->addFile($realPath, $relativePath);
                            }
                        }
                        $zip->close();
                    } else {
                        throw new \Exception("Could not create ZipArchive.");
                    }
                } catch (\Exception $ex) {
                    \Log::error("SFTP Dir Zip Failed: " . $ex->getMessage());
                    return response()->json(['error' => 'Failed to package remote directory.'], 500);
                } finally {
                    // Clean up downloaded directory
                    $deleteFolder = function($dir) use (&$deleteFolder) {
                        if (!file_exists($dir)) return;
                        $files = array_diff(scandir($dir), ['.', '..']);
                        foreach ($files as $file) {
                            (is_dir("$dir/$file")) ? $deleteFolder("$dir/$file") : @unlink("$dir/$file");
                        }
                        @rmdir($dir);
                    };
                    $deleteFolder($tempDir);
                }

                return response()->download($tempZipPath, $fileName, [
                    'Content-Type' => 'application/zip',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ])->deleteFileAfterSend(true);
            }

            // It is a single file on SFTP
            $originalFileName = basename($filePath);
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);

            $size = $upload->delivered_file_size ?: null;
            if (!$size) {
                try {
                    $size = $disk->size($filePath);
                } catch (\Exception $e) {
                    \Log::warning("downloadDelivered: Could not stat SFTP size for [{$filePath}]: " . $e->getMessage());
                    $size = null;
                }
            }

            $mimeType = 'application/octet-stream';
            if (Str::endsWith(strtolower($originalFileName), '.zip')) {
                $mimeType = 'application/zip';
            }

            return response()->stream(function() use ($disk, $filePath) {
                while (ob_get_level() > 0) ob_end_clean();
                $stream = $disk->readStream($filePath);
                if ($stream) {
                    while (!feof($stream)) {
                        echo fread($stream, 4194304); 
                        flush();
                    }
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
                'Content-Length' => $size,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        // For Google Drive, we can't easily proxy stream it without massive memory usage if it's large,
        // but we can redirect to a sharing link or use short-lived URLs.
        // For now, redirecting to the GDrive link if it's a link.
        if ($upload->delivery_method === 'google_drive' || $upload->delivery_method === 'onedrive') {
            $provider = ($upload->delivery_method === 'google_drive') ? 'Google Drive' : 'OneDrive';
            return response()->json(['error' => "Please download directly from the {$provider} link shared with you."], 400);
        }

        return response()->json(['error' => 'Unsupported download method.'], 400);
    }
    public function syncSftpMetadata($id)
    {
        $query = \App\Models\ClientUpload::where('id', $id);
        // 🚀 ADMIN OVERRIDE (v128): Allow admins to sync anyone's data
        if (\Auth::user()->role !== 'admin') {
            $query->where('created_by_email', \Auth::user()->email);
        }
        $upload = $query->firstOrFail();

        if (!str_contains($upload->upload_type, 'sftp')) {
            return response()->json(['success' => false, 'message' => 'Not an SFTP project.']);
        }

        // 🚀 SMART-PATH SYNC (v199): Use the project creator's identity, not the current session's.
        $creator = \App\Models\User::where('email', $upload->created_by_email)->first();
        $sftpUser = $creator ? ($creator->sftp_username ?: \Str::slug($creator->name)) : 'guest';
        
        $baseUploadRoot = rtrim(env('SYSTEM_SSH_STORAGE_ROOT', '/srv/sftpgo/data'), '/');
        $remotePath = "{$baseUploadRoot}/uploads/{$sftpUser}/{$upload->project_id}";

        try {
            $port = (int) env('SYSTEM_SSH_PORT', 22);
            $sshHost = env('SYSTEM_SSH_HOST', config('filesystems.disks.sftp_delivery.host'));
            $sshUser = env('SYSTEM_SSH_USERNAME', 'root');
            $sshPass = env('SYSTEM_SSH_PASSWORD');
            $tempSsh = new \phpseclib3\Net\SSH2($sshHost, $port);
            if ($tempSsh->login($sshUser, $sshPass)) {
                $ssh = $tempSsh;
            }

            if (!$ssh) {
                return response()->json(['success' => false, 'message' => 'Cloud connection failed on all ports.']);
            }

            \Log::info("Smart Scan Active. Path: " . $remotePath);

            // 1. Total Size (du -sb)
            $sizeOutput = $ssh->exec("du -sb " . escapeshellarg($remotePath) . " 2>&1");
            \Log::info("Smart Scan Raw Output: " . $sizeOutput);

            if (str_contains($sizeOutput, 'No such file')) {
                 return response()->json(['success' => false, 'message' => 'Project folder not found on server yet.']);
            }
            $totalBytes = (int)preg_split('/\s+/', trim($sizeOutput))[0];

            // 2. Photo Count
            // 🚀 UNIVERSAL PHOTO DETECTION (v237): Support for all pro formats (JPG, TIF, RAW, POS, etc.)
            $imgCmd = "find " . escapeshellarg($remotePath) . " -type f \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' -o -iname '*.tif' -o -iname '*.tiff' -o -iname '*.dng' -o -iname '*.webp' -o -iname '*.cr2' -o -iname '*.cr3' -o -iname '*.nef' -o -iname '*.arw' -o -iname '*.orf' -o -iname '*.raf' -o -iname '*.rw2' -o -iname '*.bmp' -o -iname '*.pos' -o -iname '*.exif' -o -iname '*.txt' -o -iname '*.csv' \) | wc -l";
            $imgCount = (int)trim($ssh->exec($imgCmd));
            
            // ZIP Contents (Using Python3 as fallback for missing unzip command)
            $zipFindCmd = "find " . escapeshellarg($remotePath) . " -type f -iname '*.zip'";
            $zipFiles = explode("\n", trim($ssh->exec($zipFindCmd)));
            $innerCount = 0;
            
            foreach ($zipFiles as $zipPath) {
                if (empty($zipPath)) continue;
                
                // 🚀 SMART ZIP SCAN (v129): Using Python3 to peek inside ZIPs
                // This bypasses the 'unzip' missing command issue found in diagnostics
                $pyCmd = "python3 -c \"import sys, zipfile, re; z = zipfile.ZipFile(sys.argv[1]); print(len([f for f in z.namelist() if re.search(r'\\.(jpg|jpeg|png|tif|tiff|dng|webp)$', f, re.I)]))\" " . escapeshellarg($zipPath);
                $res = $ssh->exec($pyCmd);
                $innerCount += (int)trim($res);
            }

            $finalCount = $imgCount + $innerCount;
            \Log::info("Smart Scan Result: {$totalBytes} bytes, {$finalCount} photos ({$imgCount} direct, {$innerCount} in zips)");

            // Update Database
            $upload->total_size_bytes = $totalBytes;
            $upload->file_count = $finalCount;
            $upload->save();

            return response()->json([
                'success' => true,
                'size' => $totalBytes,
                'count' => $finalCount,
                'formattedSize' => $this->formatBytes($totalBytes)
            ]);

        } catch (\Exception $e) {
            \Log::error("Sync Metadata Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()]);
        }
    }

    public function syncGoogleDriveMetadata($id)
    {
        $query = ClientUpload::where('id', $id);
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin') {
            $query->where('created_by_email', Auth::user()->email);
        }
        $upload = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'size' => $upload->total_size_bytes,
            'count' => $upload->file_count,
            'formattedSize' => $this->formatBytes($upload->total_size_bytes)
        ]);
    }

    public function syncOneDriveMetadata($id)
    {
        $query = ClientUpload::where('id', $id);
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'superadmin') {
            $query->where('created_by_email', Auth::user()->email);
        }
        $upload = $query->firstOrFail();

        if ($upload->upload_type !== 'onedrive' || !$upload->onedrive_link) {
            return response()->json(['success' => false, 'message' => 'Not a OneDrive project.']);
        }

        try {
            $this->syncOneDriveMetadataInternal($upload, $upload->onedrive_item_id, $upload->onedrive_drive_id);
            $upload->refresh(); // 🚀 UI SYNC (v286): Ensure latest DB values are returned
            
            return response()->json([
                'success' => true,
                'size' => $upload->total_size_bytes,
                'count' => $upload->file_count,
                'formattedSize' => $this->formatBytes($upload->total_size_bytes)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'OneDrive sync failed: ' . $e->getMessage()]);
        }
    }

    private function syncOneDriveMetadataInternal($upload, $itemId = null, $driveId = null)
    {
        try {
            \Log::info("OneDrive Sync [Public Mode]: Starting for {$upload->onedrive_link}");
            
            $longUrl = $this->expandOneDriveUrl($upload->onedrive_link);

            // 🚀 METHOD 1: Unauthenticated Public API (Straight-forward, NO setup required)
            try {
                $shareId = 'u!' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($longUrl));
                \Log::info("OneDrive Sync: Trying Unauthenticated API with ShareID: {$shareId}");
                
                $apiRes = \Illuminate\Support\Facades\Http::get("https://api.onedrive.com/v1.0/shares/{$shareId}/root");
                
                if ($apiRes->successful()) {
                    $itemData = $apiRes->json();
                    $totalCount = 0;
                    $totalSizeBytes = $itemData['size'] ?? 0;
                    
                    if (isset($itemData['file'])) {
                        $fileName = $itemData['name'] ?? '';
                        if (str_ends_with(strtolower($fileName), '.zip')) {
                            $downloadUrl = $itemData['@microsoft.graph.downloadUrl'] ?? $itemData['@content.downloadUrl'] ?? null;
                            if ($downloadUrl) {
                                $zipInfo = $this->peekOneDriveZipCount($downloadUrl);
                                $totalCount = $zipInfo['count'];
                            }
                        } else {
                            $totalCount = 1;
                        }
                    } else {
                        // It's a folder, run recursive scan
                        $scanFolder = function($url) use (&$scanFolder, &$totalCount, &$totalSizeBytes, $shareId) {
                            while ($url) {
                                $res = \Illuminate\Support\Facades\Http::get($url);
                                if ($res->failed()) break;
                                $data = $res->json();
                                foreach ($data['value'] ?? [] as $item) {
                                    if (isset($item['folder'])) {
                                        $scanFolder("https://api.onedrive.com/v1.0/shares/{$shareId}/items/{$item['id']}/children");
                                    } else {
                                        $name = strtolower($item['name']);
                                        if (preg_match('/\.(jpg|jpeg|png|tif|tiff|dng|webp|cr2|cr3|nef|arw|orf|raf|rw2|bmp|pos|exif|txt|csv)$/i', $name)) {
                                            $totalCount++;
                                        }
                                    }
                                }
                                $url = $data['@odata.nextLink'] ?? null;
                            }
                        };
                        $scanFolder("https://api.onedrive.com/v1.0/shares/{$shareId}/root/children");
                    }
                    
                    if ($totalSizeBytes > 1024) {
                        \Log::info("OneDrive Sync: Unauthenticated API Success. Size: {$totalSizeBytes}");
                        $upload->total_size_bytes = $totalSizeBytes;
                        $upload->file_count = $totalCount;
                        $upload->save();
                        return true;
                    }
                }
            } catch (\Exception $apiEx) {
                \Log::warning("OneDrive Sync: Unauthenticated API failed: " . $apiEx->getMessage());
            }

            // 🚀 METHOD 2: Human Mimicry (Fallback for SharePoint or strict links)
            \Log::info("OneDrive Sync: Falling back to Mimicry Method");
            
            // 1. Setup Guzzle client to mimic a real browser
            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'cookies' => $jar,
                'allow_redirects' => true,
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ]
            ]);

            // 2. Fetch the shared link's HTML page
            $resp = $client->get($longUrl);
            $html = (string)$resp->getBody();
            
            // 3. Try to extract direct downloadUrl from JSON
            if (preg_match('/"downloadUrl":"([^"]+)"/', $html, $m)) {
                $directUrl = str_replace('\\u0026', '&', $m[1]);
                \Log::info("OneDrive Sync: Found direct downloadUrl in JSON!");
                
                $zipInfo = $this->peekOneDriveZipCount($directUrl, $jar);
                if ($zipInfo['size'] > 1024) { // Real files are > 1KB
                    $upload->total_size_bytes = $zipInfo['size'];
                    $upload->file_count = $zipInfo['count'];
                    $upload->save();
                    return true;
                }
            }

            // 4. Try to extract resid and authkey to build the public download URL
            $resid = '';
            $authkey = '';
            
            if (preg_match('/resid=([a-zA-Z0-9!%_-]+)/i', $html, $m)) $resid = urldecode($m[1]);
            if (preg_match('/authkey=([a-zA-Z0-9!%_-]+)/i', $html, $m)) $authkey = urldecode($m[1]);
            
            // Removed erroneous extraction of e= parameter as authkey
            // If not found in HTML, try URL expansion
            if (!$resid || !$authkey) {
                $expandedUrl = $this->expandOneDriveUrl($upload->onedrive_link);
                if (preg_match('/[?&](resid|id)=([^&]+)/i', $expandedUrl, $m)) $resid = $m[2];
                if (preg_match('/[?&]authkey=([^&]+)/i', $expandedUrl, $m)) $authkey = $m[1];
                
                // If authkey is still missing, check shortlink path segments
                if (!$authkey && str_contains($upload->onedrive_link, '1drv.ms/u/c/')) {
                    $pathParts = explode('/', parse_url($upload->onedrive_link, PHP_URL_PATH));
                    $potentialKey = end($pathParts);
                    if ($potentialKey && strlen($potentialKey) > 10) {
                        $authkey = '!' . (str_starts_with($potentialKey, 'A') ? '' : 'A') . $potentialKey;
                    }
                }
            }

            // Extract CID
            $cid = '';
            if ($resid && str_contains($resid, '!')) {
                $cid = explode('!', $resid)[0];
            }
            
            \Log::info("OneDrive Sync: Extracted params: resid={$resid}, authkey={$authkey}, cid={$cid}");
            
            if ($resid && $authkey) {
                $cidParam = $cid ? "&cid={$cid}" : "";
                $publicDownloadUrl = "https://onedrive.live.com/download?resid={$resid}&authkey={$authkey}{$cidParam}";
                \Log::info("OneDrive Sync: Constructed public URL: {$publicDownloadUrl}");
                
                $zipInfo = $this->peekOneDriveZipCount($publicDownloadUrl, $jar);
                if ($zipInfo['size'] > 1024) {
                    $upload->total_size_bytes = $zipInfo['size'];
                    $upload->file_count = $zipInfo['count'];
                    $upload->save();
                    return true;
                }
            }
            
            // 5. If all else fails, use the ?download=1 appended URL
            $fallbackUrl = $this->convertToDirectOneDriveUrl($upload->onedrive_link);
            \Log::info("OneDrive Sync: Using fallback download URL: {$fallbackUrl}");
            $zipInfo = $this->peekOneDriveZipCount($fallbackUrl, $jar);
            if ($zipInfo['size'] > 1024) {
                $upload->total_size_bytes = $zipInfo['size'];
                $upload->file_count = $zipInfo['count'];
                $upload->save();
                return true;
            }

            // 🚀 METADATA PROTECTION (v290): Instead of blindly setting to 0, check if we have 
            // accurate data in the DB or image_metadata JSON from a previous Nitro/SFTP upload.
            if ($upload->total_size_bytes > 1024 && $upload->file_count > 0) {
                \Log::info("OneDrive Sync Protection [{$upload->project_id}]: Preserving existing metrics over failed sync.");
                return true;
            }

            // 🚀 RETROACTIVE RECOVERY (v290): Check image_metadata JSON
            $meta = is_string($upload->image_metadata) ? json_decode($upload->image_metadata, true) : $upload->image_metadata;
            if (isset($meta['count']) && (int)$meta['count'] > 0) {
                \Log::info("OneDrive Sync Recovery [{$upload->project_id}]: Restoring count from metadata JSON.");
                $upload->file_count = (int)$meta['count'];
                $upload->save();
                return true;
            }

            \Log::warning("OneDrive Sync: Could not determine file size or count. Setting to 0 as absolute fallback.");
            $upload->total_size_bytes = 0;
            $upload->file_count = 0;
            $upload->save();
            return true;

        } catch (\Exception $e) {
            \Log::error("OneDrive Sync Error [{$upload->project_id}]: " . $e->getMessage());
            throw $e;
        }
    }


    private function extractGoogleDriveFolderId($link)
    {
        if (preg_match('/folders\/([a-zA-Z0-9-_]+)/', $link, $matches)) {
            return $matches[1];
        }
        if (preg_match('/id=([a-zA-Z0-9-_]+)/', $link, $matches)) {
            return $matches[1];
        }
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $link, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function formatBytes($bytes) {
        if ($bytes <= 0) return '0 Bytes';
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
    }


    public static function expandOneDriveUrl($url)
    {
        try {
            $currentUrl = $url;
            \Log::info("OneDrive Expansion Start: " . $url);
            
            for ($i = 0; $i < 5; $i++) {
                $ch = curl_init($currentUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_HEADER, true);
                // 🚀 GET-BASED EXPANSION (v286): Some servers reject NOBODY/HEAD requests. 
                // We use GET with a tiny range to get the headers safely.
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Range: bytes=0-0', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $response = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
                curl_close($ch);

                \Log::info("OneDrive Expansion Step {$i}: Status {$status}, Redirect: {$redirectUrl}");

                if ($status >= 300 && $status < 400 && $redirectUrl) {
                    if (str_starts_with($redirectUrl, '/')) {
                        $parsed = parse_url($currentUrl);
                        $currentUrl = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? 'onedrive.live.com') . $redirectUrl;
                    } else {
                        $currentUrl = $redirectUrl;
                    }
                } else {
                    // Check if the current URL already has what we need
                    if (str_contains($currentUrl, 'resid=') || str_contains($currentUrl, 'id=')) {
                        break;
                    }
                    break;
                }
            }
            \Log::info("OneDrive Expansion Final: " . $currentUrl);
            return $currentUrl;
        } catch (\Exception $e) {
            \Log::warning("OneDrive URL Expansion Failed: " . $e->getMessage());
        }
        return $url;
    }

    public static function convertToDirectOneDriveUrl($url)
    {
        if (!$url) return $url;
        
        if (str_contains($url, 'onedrive.live.com') || str_contains($url, '1drv.ms') || str_contains($url, 'sharepoint.com')) {
            if (!str_contains($url, 'download=1')) {
                $separator = str_contains($url, '?') ? '&' : '?';
                return $url . $separator . 'download=1';
            }
        }
        
        return $url;
    }

    private function peekOneDriveZipCount($downloadUrl, $jar = null)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'cookies' => $jar ?: true,
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ]
            ]);

            \Log::info("OneDrive ZIP Peek: Fetching size for URL: " . substr($downloadUrl, 0, 100) . "...");

            // 🚀 SIZE DETECTION: Try to get Content-Length from headers via GET Range 0-0
            $resp = $client->get($downloadUrl, [
                'headers' => ['Range' => 'bytes=0-0'],
                'allow_redirects' => true,
                'http_errors' => false // Prevent throwing exceptions on 4xx/5xx
            ]);
            
            $status = $resp->getStatusCode();
            if ($status >= 400) {
                \Log::warning("OneDrive ZIP Peek: Request failed with status {$status}");
                return ['count' => 0, 'size' => 0];
            }

            $contentType = strtolower($resp->getHeaderLine('Content-Type'));
            if (str_contains($contentType, 'text/html')) {
                \Log::warning("OneDrive ZIP Peek: URL returned HTML page instead of file. (Likely landing page size issue)");
                return ['count' => 0, 'size' => 0];
            }
            
            $size = 0;
            if ($resp->hasHeader('Content-Range')) {
                $range = $resp->getHeaderLine('Content-Range');
                if (preg_match('/\/(\d+)/', $range, $m)) $size = (int)$m[1];
            } elseif ($resp->hasHeader('Content-Length')) {
                $size = (int)$resp->getHeaderLine('Content-Length');
            }

            \Log::info("OneDrive ZIP Peek: Detected Size: {$size}");
            if ($size <= 0) return ['count' => 0, 'size' => 0];

            // 🚀 ZIP PEEK: Fetch the last 1MB (Central Directory)
            $rangeSize = 1048576; 
            $start = max(0, $size - $rangeSize);
            $rangeHeader = "bytes={$start}-" . ($size - 1);

            $resp = $client->get($downloadUrl, [
                'headers' => ['Range' => $rangeHeader],
                'allow_redirects' => true,
                'http_errors' => false
            ]);

            if ($resp->getStatusCode() !== 206) {
                \Log::warning("OneDrive Range Request Refused (Status {$resp->getStatusCode()}). Likely security block.");
                if ($resp->getStatusCode() === 200) return ['count' => 0, 'size' => $size];
                return ['count' => 0, 'size' => 0];
            }

            $data = (string)$resp->getBody();
            if (empty($data)) return ['count' => 0, 'size' => $size];

            // 🚀 SMART SIGNATURE SCAN (v288): Search for ZIP Central Directory File Headers
            $count = substr_count($data, "PK\x01\x02");
            
            // Fallback to extension counting if signatures are missing in the fetched chunk
            if ($count === 0) {
                $extensions = ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'dng', 'webp', 'cr2', 'cr3', 'nef', 'arw', 'orf', 'raf', 'rw2', 'bmp', 'pos', 'exif', 'txt', 'csv'];
                $pattern = '/\.(' . implode('|', $extensions) . ')/i';
                $count = preg_match_all($pattern, $data);
            }

            \Log::info("OneDrive ZIP Peek Success: Found {$count} files.");
            return ['count' => $count, 'size' => $size];
        } catch (\Exception $e) {
            \Log::error("OneDrive ZIP Peek Error: " . $e->getMessage());
            return ['count' => 0, 'size' => 0];
        }
    }
    public function acceptDisclaimer(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // v176: Log the acceptance for legal proof
        DisclaimerAcceptance::create([
            'user_id' => $user->id,
            'project_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $isAdmin = ($user->role === 'admin' || $user->role === 'superadmin');

        $query = ClientUpload::where('id', $id);
        if (!$isAdmin) {
            $query->where('created_by_email', $user->email);
        }

        $upload = $query->firstOrFail();

        $request->validate([
            'project_title' => 'required|string|max:255',
            'project_description' => 'nullable|string',
        ]);

        $upload->update([
            'project_title' => $request->project_title,
            'project_description' => $request->project_description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project metadata updated successfully.',
            'project' => $upload
        ]);
    }

    public function getStorageQuota()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $used = ClientUpload::calculateUserStorageUsed($user->email);
        $limit = ClientUpload::getStorageLimitBytes($user->email);

        return response()->json([
            'success' => true,
            'used_bytes' => $used,
            'limit_bytes' => $limit,
            'remaining_bytes' => max(0, $limit - $used),
            'percent_used' => $limit > 0 ? round(($used / $limit) * 100, 2) : 0,
            'has_exceeded' => $used >= $limit
        ]);
    }
    
    public function getPreviewTilesetConfig($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // 1. Find the project belonging to the logged-in user
        $record = ClientUpload::where('created_by_email', $user->email)
            ->where(function($query) use ($id) {
                $query->where('id', $id)->orWhere('project_id', $id);
            })->firstOrFail();

        // 2. Security validation: Verify the admin has actually delivered the project
        $status = strtolower($record->request_status);
        if (!in_array($status, ['sent', 'completed'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Processing asset calculations pending authorization layers.'
            ], 403);
        }

        // 3. Resolve the path folder name for the user's home directory folder structure
        $sftpUser = $user->sftp_username ?: \Illuminate\Support\Str::slug($user->name);

        // Dynamic path resolution from delivery path if populated, with chroot fallback
        $deliveryPath = $record->sftp_delivery_path;
        if ($deliveryPath) {
            $directory = dirname($deliveryPath);
            $absoluteTilesetPath = rtrim($directory, '/') . '/tileset.json';
        } else {
            $absoluteTilesetPath = rtrim(env('SYSTEM_SSH_STORAGE_ROOT', '/home/dataportal/sftpgo/sftpgo_home/data'), '/') 
                . "/uploads/" . $sftpUser . "/" . $record->project_id . "/delivered/tileset.json";
        }

        // Strip config root dynamically to yield target relative path for storage operations
        $root = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
        $relativeTilesetPath = $absoluteTilesetPath;
        if (\Illuminate\Support\Str::startsWith($absoluteTilesetPath, $root)) {
            $relativeTilesetPath = \Illuminate\Support\Str::after($absoluteTilesetPath, $root);
        }
        $relativeTilesetPath = ltrim($relativeTilesetPath, '/');
        
        // 4. Verify that the processed 3D dataset actually exists on disk
        $disk = Storage::disk('sftp_delivery');
        if (!$disk->exists($relativeTilesetPath)) {
            return response()->json([
                'success' => false, 
                'message' => 'Processed preview tileset compilation targets not found on target disk layer structure.',
                'fallback_coordinates' => [
                    'latitude' => $record->latitude,
                    'longitude' => $record->longitude
                ]
            ], 404);
        }

        // 5. Generate secure, session-authorized wildcard URL to stream preview assets without exposing direct downloads
        return response()->json([
            'success' => true,
            'project_id' => $record->project_id,
            'title' => $record->project_title,
            'latitude' => $record->latitude,
            'longitude' => $record->longitude,
            'tileset_url' => route('viewer_assets', ['path' => $relativeTilesetPath])
        ]);
    }

    public function streamViewerAsset($path)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Security check: Clients can only access paths under their own uploads/{sftp_username}/ directory
        if ($user->role !== 'admin' && $user->role !== 'superadmin') {
            $sftpUser = $user->sftp_username ?: \Illuminate\Support\Str::slug($user->name);
            $prefix = "uploads/" . $sftpUser . "/";
            if (!\Illuminate\Support\Str::startsWith($path, $prefix)) {
                return response()->json(['error' => 'Unauthorized access to asset path.'], 403);
            }
        }

        $disk = Storage::disk('sftp_delivery');
        if (!$disk->exists($path)) {
            abort(404);
        }

        // Guess content type for Cesium 3D Tiles pipeline streaming
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contentTypes = [
            'json' => 'application/json',
            'b3dm' => 'application/octet-stream',
            'cmpt' => 'application/octet-stream',
            'i3dm' => 'application/octet-stream',
            'pnts' => 'application/octet-stream',
            'glb'  => 'model/gltf-binary',
            'gltf' => 'model/gltf+json',
            'wasm' => 'application/wasm',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        $contentType = $contentTypes[$ext] ?? 'application/octet-stream';

        // Low-memory streaming from SFTP delivery disk directly to browser response buffer
        return response()->stream(function() use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'max-age=86400',
        ]);
    }

}
