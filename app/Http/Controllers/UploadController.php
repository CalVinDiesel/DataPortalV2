<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\CloudinaryHelper;   // ✅ new import

class UploadController extends Controller
{
    // ─── existing: init() ────────────────────────────────────────────────────
    public function init(Request $request)
    {
        $request->validate([
            'projectTitle'   => 'required|string',
            'projectID'      => 'required|string',
            'totalFiles'     => 'required|integer',
            'totalSizeBytes' => 'required|integer',
        ]);

        $uploadId = Str::random(16);
        session()->put("upload_{$uploadId}_metadata", $request->all());

        // AUTO-CREATE SFTP DIRECTORIES: Ensure consistency between Browser and SFTP workflows
        try {
            $sftpDisk = Storage::disk('sftp_delivery');
            $projectId = $request->projectID;

            // 1. Raw Upload Folder
            $uploadPath = 'uploads/' . $projectId;
            if (!$sftpDisk->exists($uploadPath)) {
                $sftpDisk->makeDirectory($uploadPath);
                $sftpDisk->put($uploadPath . '/.ready_for_browser_data', 'Files from web portal will arrive here.');
            }

            // 2. Processed Delivery Folder
            $deliveryPath = 'deliveries/' . $projectId;
            if (!$sftpDisk->exists($deliveryPath)) {
                $sftpDisk->makeDirectory($deliveryPath);
                $sftpDisk->put($deliveryPath . '/.ready_for_processed_model', 'Admin will drag the result here.');
            }
        } catch (\Exception $e) {
            \Log::warning('Could not auto-create SFTP consistency directories: ' . $e->getMessage());
        }

        return response()->json([
            'success'  => true,
            'uploadId' => $uploadId
        ]);
    }

    // ─── existing: chunk() ───────────────────────────────────────────────────
    public function chunk(Request $request)
    {
        $request->validate([
            'uploadId'    => 'required|string',
            'chunkIndex'  => 'required|integer',
            'totalChunks' => 'required|integer',
            'chunk'       => 'required|file',
            'filename'    => 'required|string',
        ]);

        $uploadId   = $request->uploadId;
        $chunkIndex = $request->chunkIndex;
        $fileName   = $request->filename;

        $path      = "temp_uploads/{$uploadId}/" . dirname($fileName);
        $chunkName = basename($fileName) . ".part{$chunkIndex}";

        $request->file('chunk')->storeAs($path, $chunkName);

        return response()->json(['success' => true]);
    }

    // ─── new: assembleFile() ────────────────────────────────────────────────
    public function assembleFile(Request $request)
    {
        // Increase limits for processing
        set_time_limit(0); 
        ini_set('memory_limit', '1G');

        $request->validate([
            'uploadId'    => 'required|string',
            'filename'    => 'required|string',
            'totalChunks' => 'required|integer',
        ]);

        $uploadId = $request->uploadId;
        $originalRelativePath = $request->filename;
        $totalChunks = $request->totalChunks;

        $metadata = session()->get("upload_{$uploadId}_metadata");
        if (!$metadata) {
            return response()->json(['success' => false, 'message' => 'Session expired'], 400);
        }

        $projectId = $metadata['projectID'];

        try {
            // The final destination on the SFTP server.
            $sftpDest = "uploads/{$projectId}/{$originalRelativePath}";

            // Assemble the file locally first to avoid network latency issues during assembly
            $assembledPath = Storage::disk('local')->path("temp_uploads/{$uploadId}/" . dirname($originalRelativePath) . "/" . basename($originalRelativePath));
            $assembledDir = dirname($assembledPath);
            if (!file_exists($assembledDir)) {
                mkdir($assembledDir, 0755, true);
            }
            
            $out = fopen($assembledPath, 'wb');
            if (!$out) {
                throw new \Exception("Could not open local file for assembly: {$originalRelativePath}");
            }

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = Storage::disk('local')->path(
                    "temp_uploads/{$uploadId}/" . dirname($originalRelativePath) . "/" . basename($originalRelativePath) . ".part{$i}"
                );

                if (file_exists($chunkPath)) {
                    $in = fopen($chunkPath, 'rb');
                    if ($in) {
                        stream_copy_to_stream($in, $out);
                        fclose($in);
                    }
                    // Delete the local chunk file immediately after it's appended.
                    @unlink($chunkPath);
                } else {
                    fclose($out);
                    throw new \Exception("Upload failed: Missing chunk #{$i} for file {$originalRelativePath}.");
                }
            }
            fclose($out);

            // Now upload the completely assembled file to SFTP via stream
            $stream = fopen($assembledPath, 'rb');
            if (!$stream) {
                throw new \Exception("Could not open assembled file for SFTP upload: {$originalRelativePath}");
            }
            
            // Optimization: Remove exists() check, put() will overwrite by default.
            Storage::disk('sftp_delivery')->put($sftpDest, $stream);
            
            if (is_resource($stream)) {
                fclose($stream);
            }

            // Remove the locally assembled file
            @unlink($assembledPath);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error("File assembly failed for upload {$uploadId}, file {$originalRelativePath}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── existing: finalize() ────────────────────────────────────────────────
    public function finalize(Request $request)
    {
        $request->validate([
            'uploadId'     => 'required|string',
            'files_mapping' => 'required|array',
        ]);

        $uploadId = $request->uploadId;
        $metadata = session()->get("upload_{$uploadId}_metadata");

        if (!$metadata) {
            return response()->json(['success' => false, 'message' => 'Session expired'], 400);
        }

        $projectTitle = $metadata['projectTitle'];
        $projectId    = $metadata['projectID'];

        $finalFilePaths = [];
        foreach ($request->files_mapping as $fileInfo) {
            $finalFilePaths[] = "uploads/{$projectId}/" . $fileInfo['filename'];
        }

        // Cleanup any remaining temporary directory if exists
        Storage::disk('local')->deleteDirectory("temp_uploads/{$uploadId}");

        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        try {
            $upload = ClientUpload::create([
                'project_id'          => $projectId,
                'project_title'       => $projectTitle,
                'project_description' => $metadata['projectDescription'] ?? null,
                'upload_type'         => 'browser',
                'file_count'          => count($finalFilePaths),
                'file_paths'          => $finalFilePaths,
                'camera_models'       => $metadata['cameraModels'] ?? null,
                'capture_date'        => $metadata['captureDate'] ?? null,
                'organization_name'   => $metadata['organizationName'] ?? 'Self',
                'created_by_email'    => Auth::user()->email,
                'request_status'      => 'pending',
                'latitude'            => $metadata['latitude'] ?? null,
                'longitude'           => $metadata['longitude'] ?? null,
                'category'            => $metadata['category'] ?? null,
                'output_categories'   => $metadata['outputCategory'] ?? null,
                'image_metadata'      => $metadata['imageMetadata'] ?? null,
                'total_size_bytes'    => $metadata['totalSizeBytes'] ?? 0,
            ]);

            session()->forget("upload_{$uploadId}_metadata");

            return response()->json([
                'success' => true,
                'message' => 'Upload finalized successfully.',
                'project' => $upload
            ]);
        } catch (\Throwable $e) {
            \Log::error("Finalization failed for upload {$uploadId}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Database error during finalization: ' . $e->getMessage()], 500);
        }
    }

    // ─── new: uploadPinImage() ───────────────────────────────────────────────
    public function uploadPinImage(Request $request)
    {
        $request->validate([
            'pin_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $cloudinary = new CloudinaryHelper();
        $imageUrl   = $cloudinary->uploadPinImage($request->file('pin_image'));

        return response()->json([
            'success' => true,
            'url'     => $imageUrl,
        ]);
    }
}