<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function init(Request $request)
    {
        $request->validate([
            'projectTitle' => 'required|string',
            'projectID' => 'required|string',
            'totalFiles' => 'required|integer',
            'totalSizeBytes' => 'required|integer',
        ]);

        $uploadId = Str::random(16);

        // Store metadata in session or a temporary DB record
        session()->put("upload_{$uploadId}_metadata", $request->all());

        return response()->json([
            'success' => true,
            'uploadId' => $uploadId
        ]);
    }

    public function chunk(Request $request)
    {
        $request->validate([
            'uploadId' => 'required|string',
            'chunkIndex' => 'required|integer',
            'totalChunks' => 'required|integer',
            'chunk' => 'required|file',
            'fileName' => 'required|string',
        ]);

        $uploadId = $request->uploadId;
        $chunkIndex = $request->chunkIndex;
        $fileName = $request->fileName;

        // Path: storage/app/temp_uploads/{uploadId}/{fileName}/{chunkIndex}
        $path = "temp_uploads/{$uploadId}/" . dirname($fileName);
        $chunkName = basename($fileName) . ".part{$chunkIndex}";

        $request->file('chunk')->storeAs($path, $chunkName);

        return response()->json(['success' => true]);
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'uploadId' => 'required|string',
            'files_mapping' => 'required|array',
        ]);

        $uploadId = $request->uploadId;
        $metadata = session()->get("upload_{$uploadId}_metadata");

        if (!$metadata) {
            return response()->json(['success' => false, 'message' => 'Session expired'], 400);
        }

        $projectTitle = $metadata['projectTitle'];
        $projectId = $metadata['projectID'];

        $finalPaths = [];
        foreach ($request->files_mapping as $fileInfo) {
            $filename = $fileInfo['filename'];
            $totalChunks = $fileInfo['totalChunks'];

            $finalPath = "uploads/{$projectId}/{$filename}";
            Storage::disk('local')->makeDirectory(dirname($finalPath));

            $outPath = Storage::disk('local')->path($finalPath);
            $out = fopen($outPath, "wb");
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = Storage::disk('local')->path("temp_uploads/{$uploadId}/" . dirname($filename) . "/" . basename($filename) . ".part{$i}");
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

        // Clean up temp dir
        Storage::disk('local')->deleteDirectory("temp_uploads/{$uploadId}");

        // Create DB record
        $upload = ClientUpload::create([
            'project_id' => $projectId,
            'project_title' => $projectTitle,
            'upload_type' => 'browser',
            'file_count' => count($finalPaths),
            'file_paths' => $finalPaths,
            'camera_models' => $metadata['cameraModels'] ?? null,
            'capture_date' => $metadata['captureDate'] ?? null,
            'organization_name' => $metadata['organizationName'] ?? 'Self',
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
        ]);

        session()->forget("upload_{$uploadId}_metadata");

        return response()->json([
            'success' => true,
            'message' => 'Upload finalized successfully.',
            'project' => $upload
        ]);
    }
}
