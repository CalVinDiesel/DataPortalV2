<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
        if (!in_array($role, ['trusted', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'SFTP upload is only available for trusted users.'], 403);
        }

        $request->validate([
            'projectTitle' => 'required|string',
            'projectDescription' => 'nullable|string',
            'lensType' => 'required|string',
            'category' => 'required|string',
            'outputCategory' => 'required|array',
        ]);

        $projectId = Str::slug($request->projectTitle) . '-' . Str::random(4);

        $upload = ClientUpload::create([
            'project_id' => $projectId,
            'project_title' => $request->projectTitle,
            'project_description' => $request->projectDescription,
            'upload_type' => 'sftp',
            'organization_name' => 'Self',
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'camera_models' => $request->lensType,
            'category' => $request->category,
            'output_categories' => $request->outputCategory,
            'delivery_method' => 'sftp',
        ]);

        // Return the user's real SFTP credentials from their account
        $sftpDetails = [
            'host'       => env('SFTP_DELIVERY_HOST', 'dl-dataportal.geovidia.my'),
            'port'       => env('SFTP_DELIVERY_PORT', 22),
            'username'   => Auth::user()->sftp_username ?? 'Not provisioned yet',
            'password'   => Auth::user()->sftp_password ?? 'Contact admin to activate SFTP access',
            'remotePath' => '/',  // User is chroot-jailed to their folder root
        ];

        return response()->json([
            'success' => true,
            'message' => 'Project provisioned successfully.',
            'sftpDetails' => $sftpDetails,
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

        if ($upload->delivery_method === 'portal' || $upload->delivery_method === 'sftp') {
            if (!Storage::disk('sftp_delivery')->exists($upload->delivered_file_path)) {
                return response()->json(['error' => 'File not found on storage server.'], 404);
            }
            return Storage::disk('sftp_delivery')->download($upload->delivered_file_path);
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

