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
            'fileName'    => 'required|string',
        ]);

        $uploadId   = $request->uploadId;
        $chunkIndex = $request->chunkIndex;
        $fileName   = $request->fileName;

        $path      = "temp_uploads/{$uploadId}/" . dirname($fileName);
        $chunkName = basename($fileName) . ".part{$chunkIndex}";

        $request->file('chunk')->storeAs($path, $chunkName);

        return response()->json(['success' => true]);
    }

    // ─── existing: finalize() ────────────────────────────────────────────────
    public function finalize(Request $request)
    {
        // Increase limits for large file processing
        set_time_limit(0); 
        ini_set('memory_limit', '1G');
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

        $finalPaths = [];
        foreach ($request->files_mapping as $fileInfo) {
            $filename    = $fileInfo['filename'];
            $totalChunks = $fileInfo['totalChunks'];

            $finalPath = "uploads/{$projectId}/{$filename}";
            Storage::disk('local')->makeDirectory(dirname($finalPath));

            $outPath = Storage::disk('local')->path($finalPath);
            $out     = fopen($outPath, "wb");

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = Storage::disk('local')->path(
                    "temp_uploads/{$uploadId}/" . dirname($filename) . "/" . basename($filename) . ".part{$i}"
                );
                if (file_exists($chunkPath)) {
                    $in = fopen($chunkPath, "rb");
                    stream_copy_to_stream($in, $out);
                    fclose($in);
                    @unlink($chunkPath);
                }
            }
            fclose($out);
            $finalPaths[] = $finalPath;
        }

        // Push assembled files to SFTP server so admin can access raw uploads
        // via WinSCP in one consistent location: /var/sftp/uploads/{project_id}/
        $sftpPaths = [];
        foreach ($finalPaths as $localPath) {
            try {
                $sftpDest = "uploads/{$projectId}/" . basename($localPath);
                $stream   = fopen(Storage::disk('local')->path($localPath), 'rb');
                Storage::disk('sftp_delivery')->put($sftpDest, $stream);
                if (is_resource($stream)) fclose($stream);
                $sftpPaths[] = $sftpDest;
            } catch (\Throwable $e) {
                \Log::warning("SFTP push failed for {$localPath}: " . $e->getMessage());
                // Non-fatal: upload still recorded in DB even if SFTP push fails
            }
        }

        Storage::disk('local')->deleteDirectory("temp_uploads/{$uploadId}");

        $upload = ClientUpload::create([
            'project_id'          => $projectId,
            'project_title'       => $projectTitle,
            'project_description' => $metadata['projectDescription'] ?? null,
            'upload_type'         => 'browser',
            'file_count'          => count($finalPaths),
            'file_paths'          => $finalPaths,
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