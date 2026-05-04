<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $uploads = ClientUpload::where('created_by_email', Auth::user()->email)
            ->where('request_status', '!=', 'user_hidden') // 🚀 USER-HIDE (v211): Don't show projects the user "deleted"
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($u) {
                $u->client_sftp_user = Auth::user()->sftp_username ?: \Illuminate\Support\Str::slug(Auth::user()->name);
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

        $upload = ClientUpload::create([
            'project_id' => $request->projectID,
            'project_title' => $request->projectTitle,
            'project_description' => $request->projectDescription,
            'upload_type' => 'sftp',
            'organization_name' => 'Self',
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'camera_models' => $request->cameraConfiguration ?? 'SFTP Upload',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'category' => $request->category,
            'output_categories' => $request->outputCategory,
            'image_metadata' => $request->imageMetadata ?? '[]',
            'capture_date' => $request->captureDate ?? now()->toDateString(),
            'delivery_method' => 'portal', 
        ]);

        // AUTO-CREATE SFTP DIRECTORIES: One for User Upload, One for Admin Delivery
        try {
            $sftpDisk = Storage::disk('sftp_delivery');
            $user = Auth::user();
            $sftpUser = $user->sftp_username ?: Str::slug($user->name);

            // 1. User Upload Folder (Locker-aware) - 🚀 PATH-SYNC (v163)
            $diskRoot = '/home/tiquan';
            
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
                'request_status' => 'pending'
            ]);

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

        $upload = ClientUpload::create([
            'project_id' => $projectId,
            'project_title' => $request->projectTitle,
            'project_description' => $request->projectDescription,
            'upload_type' => 'google_drive',
            'google_drive_link' => $link,
            'camera_models' => $request->cameraConfiguration,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'category' => $request->category,
            'output_categories' => $request->outputCategory,
            'image_metadata' => $request->imageMetadata,
            'capture_date' => $request->captureDate,
            'organization_name' => 'Self',
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'delivery_method' => 'google_drive',
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
            // UNLIMITED Memory Boost for this request
            if (function_exists('ini_set')) {
                ini_set('memory_limit', '-1'); 
            }
            set_time_limit(0);

            // Clear all output buffers to prevent memory bloat
            // 🚀 STABLE STREAM (v219): Let Laravel handle headers
            $disk = Storage::disk('sftp_delivery');
            
            // 🚀 ROOT STRIPPING (v218): The disk root is already /home/tiquan/
            // We must remove it from the absolute path stored in the DB to avoid double-pathing.
            $filePath = $upload->delivered_file_path;
            $root = config('filesystems.disks.sftp_delivery.root', '/home/tiquan/');
            if (Str::startsWith($filePath, $root)) {
                $filePath = Str::after($filePath, $root);
            }
            // Ensure no leading slash if root ended with one, or vice versa
            $filePath = ltrim($filePath, '/');

            if (!$disk->exists($filePath)) {
                // Fallback: check original path just in case
                if (!$disk->exists($upload->delivered_file_path)) {
                    return response()->json(['error' => 'File not found on storage server.'], 404);
                } else {
                    $filePath = $upload->delivered_file_path;
                }
            }

            $fileName = basename($filePath);
            
            // 🚀 EXTENSION INSURANCE (v177): Ensure filename always ends with .zip
            if (!Str::endsWith(strtolower($fileName), '.zip')) {
                $fileName .= '.zip';
            }
            
            // 🚀 FILENAME SANITIZATION: Remove any characters that could break headers
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            
            // 🚀 INSTANT START (v146): Skip slow SFTP metadata calls (size/mime) before the stream.
            // This ensures the browser shows the download bar "the next second" instead of waiting.
            $mimeType = 'application/octet-stream'; 
            $size = null;

            try {
                // 🚀 PRE-EMPTIVE SIZE (v176): Use corrected path
                $size = $disk->size($filePath);
            } catch (\Exception $e) { 
                $size = null;
            }

            return response()->stream(function() use ($disk, $filePath) {
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
            ]);
        }

        // For Google Drive, we can't easily proxy stream it without massive memory usage if it's large,
        // but we can redirect to a sharing link or use short-lived URLs.
        // For now, redirecting to the GDrive link if it's a link.
        if ($upload->delivery_method === 'google_drive') {
            return response()->json(['error' => 'Please download directly from the Google Drive link shared with you.'], 400);
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

    private function syncGoogleDriveMetadataInternal($upload, $folderId)
    {
        try {
            $config = config('filesystems.disks.google_drive');
            $client = new \Google\Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);
            $client->addScope(\Google\Service\Drive::DRIVE);

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
                        'pageSize' => 100
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
            $itemInfo = $service->files->get($folderId, ['fields' => 'id, name, size, mimeType']);
            
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
        curl_close($ch);

        if ($status !== 206 && $status !== 200) {
            \Log::warning("Zip Peeker HTTP Error: {$status}");
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
}


