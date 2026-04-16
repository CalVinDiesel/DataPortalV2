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
            ->orderBy('created_at', 'desc')
            ->get();

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
        $upload = ClientUpload::where('id', $id)
            ->where('created_by_email', Auth::user()->email)
            ->firstOrFail();

        $upload->delete();

        return response()->json(['success' => true]);
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
            
            // 1. User Upload Folder
            $uploadPath = 'uploads/' . $upload->project_id;
            if (!$sftpDisk->exists($uploadPath)) {
                $sftpDisk->makeDirectory($uploadPath);
                $sftpDisk->put($uploadPath . '/.ready_for_raw_data', 'Drag your photos into this folder.');
            }

            // 2. Admin Delivery Folder (Pre-created for you)
            $deliveryPath = 'deliveries/' . $upload->project_id;
            if (!$sftpDisk->exists($deliveryPath)) {
                $sftpDisk->makeDirectory($deliveryPath);
                $sftpDisk->put($deliveryPath . '/.ready_for_processed_model', 'Admin will drag the result here.');
            }
        } catch (\Exception $e) {
            \Log::warning('Could not auto-create SFTP directories: ' . $e->getMessage());
        }

        // Return connection details for the UI. (Personalized for the user)
        $user = Auth::user();
        $isAdmin = ($user->role === 'admin' || $user->role === 'superadmin');
        
        $sftpDetails = [
            'remotePath' => env('SFTP_DELIVERY_ROOT', '/home/tiquan/') . 'uploads/' . $upload->project_id . '/',
            'host'       => env('SFTP_DELIVERY_HOST', '172.21.107.151'),
            'port'       => env('SFTP_DELIVERY_PORT', 22),
        ];

        if ($isAdmin) {
            // Admins see the master server credentials
            $sftpDetails['username'] = env('SFTP_DELIVERY_USERNAME', 'tiquan');
            $sftpDetails['password'] = env('SFTP_DELIVERY_PASSWORD', 'ubuntu23');
        } else {
            // Generate credentials if missing
            if (!$user->sftp_username) {
                $rawPassword = Str::random(12);
                $user->sftp_username = Str::slug($user->name) . '_' . strtolower(Str::random(6));
                $user->sftp_password = password_hash($rawPassword, PASSWORD_ARGON2ID);
                $user->save();
                
                $sftpDetails['username'] = $user->sftp_username;
                $sftpDetails['password'] = $rawPassword; // Give the raw password ONCE
            } else {
                $sftpDetails['username'] = $user->sftp_username;
                $sftpDetails['password'] = '******** (Check your initial registration email or contact admin)';
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
            while (ob_get_level()) {
                ob_end_clean();
            }

            $disk = Storage::disk('sftp_delivery');
            if (!$disk->exists($upload->delivered_file_path)) {
                return response()->json(['error' => 'File not found on storage server.'], 404);
            }

            $fileName = basename($upload->delivered_file_path);
            $size = $disk->size($upload->delivered_file_path);
            $mimeType = $disk->mimeType($upload->delivered_file_path) ?: 'application/octet-stream';

            // Return a direct stream response
            return response()->stream(function() use ($disk, $upload) {
                $stream = $disk->readStream($upload->delivered_file_path);
                if ($stream) {
                    fpassthru($stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => $size,
                'X-Content-Type-Options' => 'nosniff',
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
}

