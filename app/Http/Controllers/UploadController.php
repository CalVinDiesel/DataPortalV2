<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    // ─── INITIALIZE ───────────────────────────────────────────────────────
    public function init(Request $request)
    {
        return response()->json([
            'success'  => true,
            'uploadId' => 'up_' . Str::random(10) . '_' . time()
        ]);
    }

    // ─── NITRO DUAL-ENGINE (Sharding + Multiplex) ─────────────────────────
    public function direct(Request $request)
    {
        set_time_limit(0);
        ignore_user_abort(true);

        // Mode A: High-Speed Sharding (Large Files)
        if ($request->hasFile('file_chunk')) {
            $uId   = $request->input('upload_id');
            $index = $request->input('chunk_index');
            $pId   = $request->input('project_id') ?: $request->query('projectID');
            $file  = $request->file('file_chunk');

            $nitroRoot = config('filesystems.disks.nitro.root');
            
            // 🚀 NAMESPACE SEPARATION (v182): Prevent User vs Admin data mixing
            $subFolder = (strpos($uId, 'admin-del-') === 0) ? 'delivery_shards' : 'user_uploads';
            $targetDir = $nitroRoot . '/' . $pId . '/' . $subFolder;
            
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

            $slotPath = $targetDir . '/' . $uId . '.slot' . $index;
            $out = fopen($slotPath, 'ab');
            $in  = fopen($file->getRealPath(), 'rb');
            stream_set_chunk_size($out, 1048576); // 1MB Buffer
            stream_copy_to_stream($in, $out);
            fclose($in); fclose($out);
            return response()->json(['success' => true]);
        }

        // Mode B: Gigabit Multiplex (Client Folders / Thousands of Files)
        try {
            $projectId = $request->query('projectID');
            $slotId    = $request->query('slot') ?? '0';
            $input     = fopen('php://input', 'rb');
            if (!$input) throw new \Exception("Could not open input stream");

            while (!feof($input)) {
                $header = $this->readStrict($input, 4);
                if ($header === false || strlen($header) < 4) break;
                $pathLen = unpack('V', $header)[1];

                $path = $this->readStrict($input, $pathLen);

                $sizeHeader = $this->readStrict($input, 8);
                $fileSize   = unpack('d', $sizeHeader)[1];

                $this->saveStreamFile($input, $projectId, $path, $fileSize, $slotId);
            }

            fclose($input);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Nitro Multiplex failure: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function saveStreamFile($input, $projectId, $filename, $limit = null)
    {
        $bufferSize   = 1048576;
        $localAbsPath = Storage::disk('nitro')->path("{$projectId}/{$filename}");
        $isFirstChunk = request()->query('isFirstChunk') !== 'false';

        if (!file_exists(dirname($localAbsPath))) {
            mkdir(dirname($localAbsPath), 0755, true);
        }

        $mode   = $isFirstChunk ? 'wb' : 'ab';
        $output = fopen($localAbsPath, $mode);
        if (!$output) throw new \Exception("Could not open output file: {$filename}");

        stream_set_chunk_size($input, $bufferSize);
        stream_set_chunk_size($output, $bufferSize);

        if ($limit === null) {
            while (!feof($input)) {
                $data = fread($input, $bufferSize);
                if ($data === false || $data === "") break;
                fwrite($output, $data);
            }
        } else {
            $remaining = $limit;
            while ($remaining > 0 && !feof($input)) {
                $readSize = min($remaining, $bufferSize);
                $data     = fread($input, $readSize);
                if ($data === false || $data === "") break;
                $written  = fwrite($output, $data);
                if ($written === false) break;
                $remaining -= $written;
            }
        }

        fclose($output);
    }

    private function readStrict($stream, $len)
    {
        $data = '';
        while (strlen($data) < $len && !feof($stream)) {
            $buffer = fread($stream, $len - strlen($data));
            if ($buffer === false) return false;
            $data .= $buffer;
        }
        return $data;
    }

    // ─── DATABASE & SFTP HANDOVER ────────────────────────────────────────
    public function finalize(Request $request)
    {
        $projectId = $request->projectID;
        Log::info("🚀 NITRO FINALIZE START [{$projectId}]");

        try {
            ignore_user_abort(true);
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $projectId = $request->projectID;
            $nitroRoot = config('filesystems.disks.nitro.root');
            $targetDir = $nitroRoot . '/' . $projectId;

            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

            // ── STEP 1: Identify user ────────────────────────────────────────
            // finalize route is now in 'web' middleware group → auth()->user() works
            $user  = auth()->user();
            $email = $user?->email ?? trim($request->userEmail ?? '');
            // Belt-and-suspenders: if session expired, try email lookup
            if (!$user && !empty($email)) {
                $user = \App\Models\User::where('email', $email)->first();
            }

            if ($user && !empty($user->sftp_username)) {
                $sftpUser = $user->sftp_username;
            } elseif ($user) {
                $sftpUser = Str::slug($user->name);
            } else {
                $sftpUser = !empty($email) ? Str::slug(explode('@', $email)[0]) : 'unknown-user';
            }
            $email = $user?->email ?? $email;

            // 🚀 ROLE-BASED PATHING (v164): Separate uploads/ and deliveries/
            $isAdmin = ($user && ($user->role === 'admin' || $user->role === 'superadmin'));
            
            $diskRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
            
            // 🚀 NESTED-PROJECT SYNC (v206): Ensure Nitro (browser) uploads use the exact same paths as SFTP.
            if ($isAdmin) {
                $relativeSftpPath = "uploads/{$sftpUser}/{$projectId}/delivered";
            } else {
                $relativeSftpPath = "uploads/{$sftpUser}/{$projectId}";
            }
            $absoluteSftpPath = $diskRoot . '/' . $relativeSftpPath;
            Log::info("  - Identity: user=[{$email}] sftpUser=[{$sftpUser}] path=[{$absoluteSftpPath}]");

            // ── STEP 2: Merge shards (up to 128 slots) ────────────────────
            Log::info("  - Merging Nitro shards...");
            $mergedZipPath = null;
            foreach (scandir($targetDir) as $file) {
                if (!preg_match('/\.slot0$/', $file)) continue;

                $baseName  = preg_replace('/\.slot0$/', '', $file);
                $finalPath = $targetDir . '/' . $baseName;
                $out       = fopen($finalPath, 'wb');
                if (!$out) { Log::error("  - Cannot open: {$baseName}"); continue; }

                // 🚀 DYNAMIC-MERGE (v247): Support infinite file size by finding all slots
                $i = 0;
                while (true) {
                    $slotPath = $targetDir . '/' . $baseName . '.slot' . $i;
                    if (!file_exists($slotPath)) break;
                    
                    $in = fopen($slotPath, 'rb');
                    stream_copy_to_stream($in, $out);
                    fclose($in);
                    unlink($slotPath);
                    $i++;
                }
                fclose($out);
                Log::info("  - Merged: {$baseName} ({$i} slots)");

                if (strtolower(pathinfo($baseName, PATHINFO_EXTENSION)) === 'zip') {
                    $mergedZipPath = $finalPath;
                    $zip = new \ZipArchive();
                    if ($zip->open($finalPath) !== TRUE) {
                        Log::error("  - [CORRUPT] ZIP invalid: {$baseName}");
                        @unlink($finalPath);
                        return response()->json([
                            'success' => false,
                            'error'   => "ZIP integrity check failed. Please re-upload.",
                        ], 422);
                    }
                    $zip->close();
                }
            }

            // ── STEP 3: Collect cargo (recursive — handles subdirectories) ──
            $cargoFiles     = [];
            $totalSizeBytes = 0;
            $fileCount      = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($targetDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) continue;
                $fileName = $fileInfo->getFilename();
                if (strpos($fileName, '.slot') !== false) continue;
                
                $fp = $fileInfo->getPathname();
                $cargoFiles[] = $fp;
                $totalSizeBytes += $fileInfo->getSize();

                // ── Deep Count: If it's a ZIP, look inside ────────────────
                $ext = strtolower($fileInfo->getExtension());
                if ($ext === 'zip') {
                    $zip = new \ZipArchive();
                    if ($zip->open($fp) === TRUE) {
                        $innerCount = 0;
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $stat = $zip->statIndex($i);
                            $entryName = $stat['name'];
                            
                            // Skip directories (names ending with / or \)
                            if (substr($entryName, -1) === '/' || substr($entryName, -1) === '\\') continue;
                            
                            // Skip hidden system files (macOS metadata, etc)
                            if (strpos($entryName, '__MACOSX') !== false || strpos(basename($entryName), '._') === 0) continue;
                            $innerCount++;
                        }
                        $fileCount += $innerCount;
                        $zip->close();
                    } else {
                        $fileCount++; // Fallback
                    }
                } else {
                    $fileCount++;
                }
            }

            // 🚀 STORAGE QUOTA CHECK (v310)
            if (\App\Models\ClientUpload::hasExceededStorageLimit($email, $totalSizeBytes)) {
                try {
                    $this->recursiveDelete($targetDir);
                } catch (\Exception $delEx) {
                    Log::error("Failed to delete quota-exceeded directory: " . $delEx->getMessage());
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Storage Quota Exceeded. Finalizing this upload (' . round($totalSizeBytes / (1024 * 1024 * 1024), 2) . ' GB) would exceed your ' . (\App\Models\ClientUpload::getStorageLimitBytes($email) / (1024 * 1024 * 1024)) . ' GB limit. Please delete old projects to free up space.'
                ], 422);
            }

            // ── STEP 4: Save to DB first (always succeeds) ────────────────
            // 🚀 CAMERA STANDARDIZATION (v251): Map 'Standard'/'Multiple' and enforce format
            $camConfig = $request->cameraConfig; // 'single' or 'multiple'
            $camModels = $request->cameraModels; // 'Standard', 'Multiple', or 'Thermal, RGB...'
            
            if ($camConfig === 'multiple') {
                if ($camModels === 'Multiple' || empty($camModels)) {
                    $finalCam = 'Multi-Lens';
                } elseif (!str_starts_with(strtolower($camModels), 'multi-lens')) {
                    $finalCam = 'Multi-Lens: ' . $camModels;
                } else {
                    $finalCam = $camModels;
                }
            } else {
                if ($camModels === 'Standard' || empty($camModels)) {
                    $finalCam = 'Single-Lens';
                } elseif (!str_starts_with(strtolower($camModels), 'single-lens')) {
                    $finalCam = 'Single-Lens: ' . $camModels;
                } else {
                    $finalCam = $camModels;
                }
            }

            $upload = \App\Models\ClientUpload::updateOrCreate(
                ['project_id' => $projectId],
                [
                    'project_title'       => $request->projectTitle,
                    'project_description' => $request->projectDescription,
                    'category'            => $request->category,
                    'created_by_email'    => $email,
                    'latitude'            => $request->latitude,
                    'longitude'           => $request->longitude,
                    'capture_date'        => $request->captureDate,
                    'camera_models'       => $finalCam,
                    'image_metadata'      => $request->imageMetadata,
                    'output_categories'   => json_decode($request->outputCategories, true),
                    'total_size_bytes'    => $totalSizeBytes,
                    'file_count'          => $fileCount,
                    'request_status'      => 'pending',
                    'upload_type'         => 'browser',
                    'delivery_method'     => 'portal',
                    'file_paths'          => [$absoluteSftpPath],
                    'delivered_file_path' => $absoluteSftpPath
                ]
            );

            Log::info("  - DB saved: [" . $projectId . "]");

            // 🚀 ULTIMATE-POS SCANNER (v280): Cross-Platform Deep Scan (ZIP + Recursive Subfolders)
            try {
                $metaType = strtolower($request->imageMetadata ?? '');
                if (str_contains($metaType, 'pos')) {
                    // Expanded professional GNSS/POS extension list
                    $posExtensions = ['pos', 'txt', 'csv', 'nav', 'log', 'mrk', 'obs', 'sbf', 'jps', 'rin', 'dat', 'bin'];
                    $foundPosFile = null;

                    // 1. Recursive Scan: Handles subfolders and different OS path separators
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir, \RecursiveDirectoryIterator::SKIP_DOTS));
                    foreach ($iterator as $fileInfo) {
                        if (!$fileInfo->isFile()) continue;
                        
                        $fileName = $fileInfo->getFilename();
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        // Check if file itself matches POS extensions
                        if (in_array($ext, $posExtensions)) {
                            // Normalize paths to Linux-style (/) for database consistency regardless of Host OS
                            $relativePath = str_replace([$targetDir, '\\'], ['', '/'], $fileInfo->getPathname());
                            $foundPosFile = rtrim($absoluteSftpPath, '/') . '/' . ltrim($relativePath, '/');
                            Log::info("  - [POS DETECTED]: " . $fileName . " at " . $foundPosFile);
                            break;
                        }
                        
                        // 2. Deep-Dive Inside ZIPs (Handles users uploading nested archives)
                        if ($ext === 'zip') {
                            $zip = new \ZipArchive();
                            if ($zip->open($fileInfo->getPathname()) === TRUE) {
                                for ($z = 0; $z < $zip->numFiles; $z++) {
                                    $zipEntry = $zip->getNameIndex($z);
                                    $zipEntryExt = strtolower(pathinfo($zipEntry, PATHINFO_EXTENSION));
                                    
                                    if (in_array($zipEntryExt, $posExtensions)) {
                                        // Format: /path/to/archive.zip [Internal: folder/metadata.pos]
                                        $zipRelativePath = str_replace([$targetDir, '\\'], ['', '/'], $fileInfo->getPathname());
                                        $zipSftpPath = rtrim($absoluteSftpPath, '/') . '/' . ltrim($zipRelativePath, '/');
                                        $foundPosFile = $zipSftpPath . " [Inside: " . $zipEntry . "]";
                                        Log::info("  - [POS DETECTED IN ZIP]: " . $zipEntry . " inside " . $fileName);
                                        break 2; // Found it, stop all loops
                                    }
                                }
                                $zip->close();
                            }
                        }
                    }

                    if ($foundPosFile) {
                        $upload->update(['drone_pos_file_path' => $foundPosFile]);
                    } else {
                        Log::warning("  - [POS SCAN EMPTY] No matching metadata file found in " . $projectId);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("  - [POS SCAN ERROR]: " . $e->getMessage());
            }

            // ── STEP 5: SFTP handover (NON-FATAL — data safe on disk) ─────
            $sftpSuccess = false;
            try {
                $sftp      = Storage::disk('sftp_delivery');
                $remoteDir = $relativeSftpPath;

                if (!$sftp->exists($remoteDir)) {
                    $sftp->makeDirectory($remoteDir);
                }
                foreach ($cargoFiles as $localPath) {
                    $stream = fopen($localPath, 'r');
                    // Compute path relative to targetDir to preserve subfolder directory structures
                    $relativeLocalPath = ltrim(str_replace($targetDir, '', $localPath), '/\\');
                    $remoteFile = $remoteDir . '/' . str_replace('\\', '/', $relativeLocalPath);
                    $sftp->put($remoteFile, $stream);
                    if (is_resource($stream)) fclose($stream);
                }
                $sftpSuccess = true;
                Log::info("  - SFTP Mirror complete: [{$absoluteSftpPath}]");
            } catch (\Exception $sftpEx) {
                Log::warning("  - [SFTP SKIPPED] " . $sftpEx->getMessage() . " — Files retained locally.");
            }

            // ── STEP 6: Always return success ─────────────────────────────
            Log::info("✅ NITRO FINALIZE SUCCESS [{$projectId}]");
            return response()->json([
                'success'       => true,
                'message'       => $sftpSuccess
                    ? 'Upload complete. Files mirrored to Linux server. 🛰️'
                    : 'Upload complete. Files saved locally (SFTP pending).',
                'projectID'     => $projectId,
                'sftpDelivered' => $sftpSuccess,
                'stats'         => ['bytes' => $totalSizeBytes, 'files' => $fileCount],
            ]);

        } catch (\Exception $e) {
            Log::error("🚨 NITRO FINALIZE FAILED [{$projectId}]: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Finalization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkSftpStatus(Request $request)
    {
        $projectId = $request->projectID;
        try {
            $upload = \App\Models\ClientUpload::where('project_id', $projectId)->first();
            if (!$upload) return response()->json(['success' => false, 'message' => 'Not found']);

            $user = \App\Models\User::where('email', $upload->created_by_email)->first();
            $sftpUser = $user ? ($user->sftp_username ?: Str::slug($user->name)) : 'unknown-user';
            
            $isAdmin = ($user && ($user->role === 'admin' || $user->role === 'superadmin'));
            $relativeSftpPath = $isAdmin ? "uploads/{$sftpUser}/{$projectId}/delivered" : "uploads/{$sftpUser}/{$projectId}";

            $sftp = Storage::disk('sftp_delivery');
            $exists = $sftp->exists($relativeSftpPath);

            return response()->json([
                'success' => true,
                'exists'  => $exists,
                'path'    => $relativeSftpPath
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function retrySftpHandover(Request $request)

    {
        $projectId = $request->projectID;
        Log::info("🔄 RETRY SFTP HANDOVER [{$projectId}]");

        try {
            $upload = \App\Models\ClientUpload::where('project_id', $projectId)->first();
            if (!$upload) throw new \Exception("Upload record not found.");

            $nitroRoot = config('filesystems.disks.nitro.root');
            $targetDir = $nitroRoot . '/' . $projectId;

            if (!file_exists($targetDir)) throw new \Exception("Local source directory not found: {$targetDir}");

            // Identify path (Reuse logic from finalize)
            $user = \App\Models\User::where('email', $upload->created_by_email)->first();
            $sftpUser = $user ? ($user->sftp_username ?: Str::slug($user->name)) : 'unknown-user';
            
            $isAdmin = ($user && ($user->role === 'admin' || $user->role === 'superadmin'));
            $diskRoot = rtrim(config('filesystems.disks.sftp_delivery.root', '/'), '/');
            
            if ($isAdmin) {
                $relativeSftpPath = "uploads/{$sftpUser}/{$projectId}/delivered";
            } else {
                $relativeSftpPath = "uploads/{$sftpUser}/{$projectId}";
            }
            $absoluteSftpPath = $diskRoot . '/' . $relativeSftpPath;

            $sftp = Storage::disk('sftp_delivery');
            if (!$sftp->exists($relativeSftpPath)) {
                $sftp->makeDirectory($relativeSftpPath);
            }

            $cargoFiles = [];
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir, \RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && strpos($fileInfo->getFilename(), '.slot') === false) {
                    $cargoFiles[] = $fileInfo->getPathname();
                }
            }

            foreach ($cargoFiles as $localPath) {
                $stream = fopen($localPath, 'r');
                $sftp->put($relativeSftpPath . '/' . basename($localPath), $stream);
                if (is_resource($stream)) fclose($stream);
            }

            return response()->json([
                'success' => true,
                'message' => 'Files successfully synced to SFTP server! 🛰️',
                'path'    => $absoluteSftpPath
            ]);

        } catch (\Exception $e) {
            Log::error("🚨 RETRY SFTP FAILED [{$projectId}]: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadPinImage(Request $request)
    {
        $request->validate(['pin_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        
        try {
            $file = $request->file('pin_image');
            $fileName = 'pin_' . time() . '_' . Str::lower(Str::random(10)) . '.' . Str::lower($file->getClientOriginalExtension());
            $file->move(public_path('uploads/thumbnails'), $fileName);
            $url = asset('uploads/thumbnails/' . $fileName);
            
            return response()->json([
                'success' => true,
                'url'     => $url
            ]);
        } catch (\Exception $e) {
            Log::error("🚨 Pin image upload failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadMapThumbnail(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        
        try {
            $file = $request->file('thumbnail');
            
            $fileName = 'thumbnail_' . time() . '_' . Str::lower(Str::random(10)) . '.' . Str::lower($file->getClientOriginalExtension());
            $file->move(public_path('uploads/thumbnails'), $fileName);
            $url = asset('uploads/thumbnails/' . $fileName);
            
            return response()->json([
                'success' => true,
                'url'     => $url
            ]);
        } catch (\Exception $e) {
            Log::error("🚨 Map thumbnail upload failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recursiveDelete($dir)
    {
        if (!file_exists($dir)) return;
        if (is_file($dir)) {
            @unlink($dir);
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $this->recursiveDelete("$dir/$file");
        }
        @rmdir($dir);
    }
}
