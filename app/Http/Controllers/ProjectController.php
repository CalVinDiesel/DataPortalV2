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
            // 🚀 ADMIN AUTHORITY (v211): Permanent Database Deletion
            $upload->delete();
            return response()->json(['success' => true, 'message' => 'Project permanently deleted from database.']);
        } else {
            // 🚀 USER REMOVAL (v211): Only hide from their browser view
            $upload->update(['request_status' => 'user_hidden']);
            return response()->json(['success' => true, 'message' => 'Project removed from your view.']);
        }
    }

    public function storeSftp(Request $request)
    {
        // Enforce role permission: Only trusted users and admins can use SFTP.
        $role = Auth::user()->role;
        if (!in_array($role, ['trusted', 'admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'SFTP upload is only available for trusted users.'], 403);
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
            $sftpUser = $user->sftp_username ?: Str::slug($user->name);

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
                $sshPort = (int)config('filesystems.disks.sftp_delivery.port', 2222);
                $ssh = new \phpseclib3\Net\SSH2(config('filesystems.disks.sftp_delivery.host'), $sshPort);
                if ($ssh->login(config('filesystems.disks.sftp_delivery.username'), config('filesystems.disks.sftp_delivery.password'))) {
                    $baseUploadRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
                    $fullPath = $baseUploadRoot . '/' . $uploadPathRelative;
                    $userDir = $baseUploadRoot . '/' . 'uploads/' . $sftpUser;
                    // 🚀 TIMESTAMP WAKEUP (v225): Write and delete a temp file to force filesystem/SFTPGo to update mtime
                    $ssh->exec("mkdir -p " . escapeshellarg($fullPath . "/delivered"));
                    
                    // Force update for User Root, Project Root, and Delivered Folder
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
        $sftpUser = $user->sftp_username ?: Str::slug($user->name);
        
        $sftpDetails = [
            'absolutePath' => rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/') . '/uploads/' . $sftpUser . '/' . $upload->project_id . '/',
            'clientPath'   => '/' . $upload->project_id . '/', 
            'host'         => config('filesystems.disks.sftp_delivery.host'),
            'port'         => $isAdmin ? (int)env('SFTP_DELIVERY_PORT', 2222) : (int)env('SFTP_USER_PORT', 2223),
        ];

        if ($isAdmin) {
            // Admins see the master server credentials
            $sftpDetails['username'] = config('filesystems.disks.sftp_delivery.username');
            $sftpDetails['password'] = config('filesystems.disks.sftp_delivery.password');
        } else {
            // Generate credentials if missing
            if (!$user->sftp_username) {
                $rawPassword = Str::random(12);
                $user->sftp_username = Str::slug($user->name) . '_' . strtolower(Str::random(6));
                $user->sftp_password = $rawPassword; // This will be ENCRYPTED in the DB because of the cast in User model
                $user->save();
                
                // 🚀 SFTPGO SYNC (v153): Must use a Hash (Argon2id) for SFTPGo to accept the password
                try {
                    DB::table('users')->where('username', $user->sftp_username)->update([
                        'password' => password_hash($rawPassword, PASSWORD_ARGON2ID),
                        'updated_at' => (int)(microtime(true) * 1000)
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("SFTPGo Sync failed during generation: " . $e->getMessage());
                }
                
                $sftpDetails['username'] = $user->sftp_username;
                $sftpDetails['password'] = $rawPassword; // Give the raw password ONCE
            } else {
                $sftpDetails['username'] = $user->sftp_username;
                $sftpDetails['password'] = $user->sftp_password; // 🚀 Show plain text
            }
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
        $request->validate([
            'projectTitle' => 'required|string',
            'projectDescription' => 'required|string',
            'cameraConfiguration' => 'required|string',
            'googleDriveLink' => 'required|url',
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
            'gdrive_delivery_folder_id' => $this->extractGoogleDriveFolderId($link), // 📂 Save ID to DB
        ]);

        // 🚀 AUTO-DETECTION (v150): Try to count files and size immediately
        try {
            $folderId = $this->extractGoogleDriveFolderId($link);
            if ($folderId) {
                $this->syncGoogleDriveMetadataInternal($upload, $folderId);
            }
        } catch (\Exception $e) {
            \Log::warning("GDrive Immediate Sync Failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Google Drive project created successfully.',
            'project' => $upload
        ]);
    }

    public function storeOneDrive(Request $request)
    {
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

            // 🔑 Preserve the ORIGINAL filename (with spaces) for SFTP path lookups.
            // The sanitized copy is only for the browser Content-Disposition header.
            $originalFileName = basename($filePath);
            if (!Str::endsWith(strtolower($originalFileName), '.zip')) $originalFileName .= '.zip';
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName); // safe for header

            // 🚀 LOCAL-FIRST OPTIMIZATION (v176): If both servers are on the same machine, 
            // streaming directly from the disk is INSTANT and bypasses SFTP handshake delays.
            if (file_exists($filePath) && is_readable($filePath)) {
                $size = filesize($filePath);
                return response()->stream(function() use ($filePath) {
                    while (ob_get_level() > 0) ob_end_clean();
                    $file = fopen($filePath, 'rb');
                    if ($file) {
                        fpassthru($file);
                        fclose($file);
                    }
                }, 200, [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
                    'Content-Length' => $size,
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                    'X-Accel-Buffering' => 'no',
                ]);
            }

            // Fallback to SFTP if local path is not accessible
            $disk = Storage::disk('sftp_delivery');
            
            // 🚀 ROOT STRIPPING (v218): Strip the absolute root prefix to get a relative SFTP path.
            // Use $filePath (original path) so spaces in the filename are preserved for the SFTP lookup.
            $root = config('filesystems.disks.sftp_delivery.root', '/');
            if (Str::startsWith($filePath, $root)) {
                $filePath = Str::after($filePath, $root);
            }
            $filePath = ltrim($filePath, '/');

            // 🛠️ SIZE FIX (v262): Use delivered_file_size (the actual processed ZIP size) for Content-Length.
            // total_size_bytes stores the size of the RAW CLIENT UPLOAD, not the delivered result —
            // using it here caused browsers to truncate large deliveries, producing a corrupt ZIP.
            $size = $upload->delivered_file_size ?: null;
            if (!$size) {
                // Live-stat the file from SFTP as the authoritative fallback
                try {
                    $size = $disk->size($filePath);
                } catch (\Exception $e) {
                    \Log::warning("downloadDelivered: Could not stat SFTP size for [{$filePath}]: " . $e->getMessage());
                    $size = null; // Send without Content-Length rather than a wrong value
                }
            }
            
            return response()->stream(function() use ($disk, $filePath) {
                // 🚀 BUFFER KILLER: Ensure no server-side buffering delays the first byte
                while (ob_get_level() > 0) ob_end_clean();
                
                $stream = $disk->readStream($filePath);
                if ($stream) {
                    // 🚀 HIGH-VELOCITY STREAM (v146): Use 4MB chunks for balanced throughput
                    while (!feof($stream)) {
                        echo fread($stream, 4194304); 
                        flush();
                    }
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
                'Content-Length' => $size,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Accel-Buffering' => 'no', // 🚀 NGINX INSTANT STREAM
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
        
        $baseUploadRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
        $remotePath = "{$baseUploadRoot}/uploads/{$sftpUser}/{$upload->project_id}";

        try {
            // 🚀 SMART-PORT-AWARE SCAN (v207): Try Admin port first, fallback to Client port
            $ssh = null;
            $finalRemotePath = $remotePath; // Default to absolute for Admin
            
            $portsToTry = [
                (int)env('SFTP_DELIVERY_PORT', 2222),
                (int)env('SFTP_USER_PORT', 2223)
            ];

            foreach ($portsToTry as $port) {
                $tempSsh = new \phpseclib3\Net\SSH2(config('filesystems.disks.sftp_delivery.host'), $port);
                if ($tempSsh->login(config('filesystems.disks.sftp_delivery.username'), config('filesystems.disks.sftp_delivery.password'))) {
                    $ssh = $tempSsh;
                    // Note: We use absolute paths for both ports because the master user is not jailed.
                    break;
                }
            }

            if (!$ssh) {
                return response()->json(['success' => false, 'message' => 'Cloud connection failed on all ports.']);
            }

            $remotePath = $finalRemotePath;
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

        if ($upload->upload_type !== 'google_drive' || !$upload->google_drive_link) {
            return response()->json(['success' => false, 'message' => 'Not a Google Drive project.']);
        }

        $folderId = $this->extractGoogleDriveFolderId($upload->google_drive_link);
        if (!$folderId) {
            return response()->json(['success' => false, 'message' => 'Could not extract Folder ID from link.']);
        }

        try {
            $upload->update(['gdrive_delivery_folder_id' => $folderId]); // 🚀 BACKFILL (v151): Save missing ID during sync
            $this->syncGoogleDriveMetadataInternal($upload, $folderId);
            
            return response()->json([
                'success' => true,
                'size' => $upload->total_size_bytes,
                'count' => $upload->file_count,
                'formattedSize' => $this->formatBytes($upload->total_size_bytes)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()]);
        }
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

    private function syncGoogleDriveMetadataInternal($upload, $folderId)
    {
        try {
            $config = config('filesystems.disks.google_drive');
            $client = new \Google\Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            // 🚀 SCOPE-FIRST (v291): Add scopes BEFORE fetching the token to ensure the request is authorized correctly
            $client->addScope(\Google\Service\Drive::DRIVE_READONLY);
            $client->addScope(\Google\Service\Drive::DRIVE);
            
            $refreshToken = trim($config['refreshToken'] ?? '');
            // 🚀 TOKEN REPAIR (v294): Handle multi-line tokens that might have been mangled in .env
            $refreshToken = str_replace(["\r", "\n", " "], '', $refreshToken);
            
            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (isset($token['error'])) {
                \Log::error("GDrive Token Error [{$upload->project_id}]: " . ($token['error_description'] ?? $token['error']));
                throw new \Exception("Google Drive authentication failed. Please check the refresh token.");
            }

            $service = new \Google\Service\Drive($client);

            $totalCount = 0;
            $totalSizeBytes = 0;

            // 🚀 BULLETPROOF RECURSIVE SCAN (v154): Use direct API with pagination
            $scanFolder = function($parentId) use (&$scanFolder, $service, &$totalCount, &$totalSizeBytes) {
                $pageToken = null;
                do {
                    $optParams = [
                        'q' => "'$parentId' in parents and trashed = false",
                        'fields' => 'nextPageToken, files(id, name, size, mimeType)',
                        'pageToken' => $pageToken,
                        'pageSize' => 100,
                        'supportsAllDrives' => true, // 🚀 SHARED DRIVE SUPPORT (v292)
                        'includeItemsFromAllDrives' => true
                    ];
                    $results = $service->files->listFiles($optParams);

                    foreach ($results->getFiles() as $file) {
                        $mime = $file->getMimeType();
                        if ($mime === 'application/vnd.google-apps.folder') {
                            $scanFolder($file->getId());
                        } else {
                            $name = strtolower($file->getName());
                            // 🚀 UNIVERSAL PHOTO DETECTION (v237): Support for all pro formats (JPG, TIF, RAW, etc.)
                            $isPhoto = false;
                            if (preg_match('/\.(jpg|jpeg|png|tif|tiff|dng|webp|cr2|cr3|nef|arw|orf|raf|rw2|bmp|pos|exif|txt|csv)$/i', $name)) {
                                $isPhoto = true;
                            }
                            
                            if ($isPhoto) {
                                $totalCount++;
                            }
                            $totalSizeBytes += (int)($file->getSize() ?: 0);
                        }
                    }
                    $pageToken = $results->getNextPageToken();
                } while ($pageToken);
            };

            // 🚀 SMART DETECTION (v155): Check if the ID is a single file or a folder
            $itemInfo = $service->files->get($folderId, [
                'fields' => 'id, name, size, mimeType',
                'supportsAllDrives' => true // 🚀 SHARED DRIVE SUPPORT (v292)
            ]);
            
            if ($itemInfo->getMimeType() !== 'application/vnd.google-apps.folder') {
                // It's a single file (e.g., a ZIP file)
                $totalCount = 1;
                $totalSizeBytes = (int)($itemInfo->getSize() ?: 0);
                $name = strtolower($itemInfo->getName());
                
                // 🚀 ZIP PEEKER (v236): If it's a ZIP, try to peek inside for photo count
                if (str_ends_with($name, '.zip') && $totalSizeBytes > 0) {
                    \Log::info("GDrive Zip Peeker [{$upload->project_id}]: Attempting to peek inside {$name}");
                    try {
                        $zipCount = $this->peekGoogleDriveZipCount($service, $folderId, $totalSizeBytes, $client->getAccessToken()['access_token']);
                        if ($zipCount > 0) {
                            $totalCount = $zipCount;
                            \Log::info("GDrive Zip Peeker [{$upload->project_id}]: Successfully counted {$zipCount} items inside ZIP.");
                        }
                    } catch (\Exception $e) {
                        \Log::warning("GDrive Zip Peeker Failed [{$upload->project_id}]: " . $e->getMessage());
                    }
                }
                \Log::info("GDrive Direct API Sync [{$upload->project_id}]: Single file detected: " . $itemInfo->getName());
            } else {
                // It's a folder, perform recursive scan
                $scanFolder($folderId);
            }

            \Log::info("GDrive Direct API Sync [{$upload->project_id}]: Found {$totalCount} files, total {$totalSizeBytes} bytes.");

            // 🚀 METADATA PROTECTION (v234): If we only found 1 file (likely a ZIP) but the DB already has a higher count 
            // from the original Nitro upload, do NOT overwrite the count with 1.
            $finalCount = $totalCount;
            if ($totalCount === 1) {
                // Check if we have a higher count in DB already
                if ($upload->file_count > 1) {
                    \Log::info("GDrive Sync Protection [{$upload->project_id}]: Preserving existing count ({$upload->file_count}) over single-file detection.");
                    $finalCount = $upload->file_count;
                } else {
                    // 🚀 RETROACTIVE RECOVERY: Check image_metadata JSON for the original count
                    $meta = is_string($upload->image_metadata) ? json_decode($upload->image_metadata, true) : $upload->image_metadata;
                    if (isset($meta['count']) && (int)$meta['count'] > 1) {
                        \Log::info("GDrive Sync Recovery [{$upload->project_id}]: Restoring count from metadata JSON: " . $meta['count']);
                        $finalCount = (int)$meta['count'];
                    }
                }
            }

            $upload->update([
                'file_count' => $finalCount,
                'total_size_bytes' => $totalSizeBytes
            ]);
        } catch (\Exception $e) {
            \Log::error("GDrive Direct API Sync Failed [{$upload->project_id}]: " . $e->getMessage());
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

    /**
     * 🚀 ZIP PEEKER (v236): Efficiently count files in a Google Drive ZIP using Range Requests.
     * This avoids downloading the entire file (e.g. 1.1GB) just to see the count.
     */
    private function peekGoogleDriveZipCount($service, $fileId, $fileSize, $accessToken)
    {
        $rangeSize = 131072; // Peek the last 128KB for the Central Directory
        $start = max(0, $fileSize - $rangeSize);
        $range = $start . '-' . ($fileSize - 1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Range: bytes={$range}"
        ]);

        $data = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($status !== 206 && $status !== 200) {
            \Log::warning("GDrive Zip Peeker HTTP Error: {$status}");
            return 0;
        }

        // 🚀 LANDING PAGE DETECTION (v293): If we got HTML, it's a sign-in or error page, not ZIP data
        if (str_contains(strtolower($contentType), 'text/html')) {
            \Log::warning("GDrive Zip Peeker: Received HTML instead of file media. Skipping count.");
            return 0;
        }

        // 🚀 SMART SIGNATURE SCAN (v237): Count entries that match professional image/data extensions
        // We look for common extensions immediately following the ZIP directory headers
        $extensions = ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'dng', 'webp', 'cr2', 'cr3', 'nef', 'arw', 'orf', 'raf', 'rw2', 'bmp', 'pos', 'exif', 'txt', 'csv'];
        $pattern = '/\.(' . implode('|', $extensions) . ')/i';
        
        $count = preg_match_all($pattern, $data);
        
        // Fallback: If no recognized extensions found but it's a ZIP, count the raw headers
        if ($count === 0) {
            $count = substr_count($data, "PK\x01\x02");
        }
        
        return $count;
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
        
        // 🚀 STABLE REDIRECT (v277): Simple and reliable.
        // Appending 'download=1' is the safest way to force a download across all OneDrive types.
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
}


