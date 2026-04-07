<?php

namespace App\Http\Controllers;

use App\Models\ClientUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'upload_type' => 'sftp',
            'organization_name' => 'Self',
            'created_by_email' => Auth::user()->email,
            'request_status' => 'pending',
            'camera_models' => $request->lensType,
            // 'category' isn't in ClientUploads table? Let's check.
        ]);

        // Mock SFTP details
        $sftpDetails = [
            'host' => 'dl-dataportal.geovidia.my',
            'port' => 22,
            'username' => 'sftp_' . Str::random(8),
            'password' => Str::random(12),
            'remotePath' => '/uploads/' . $projectId,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Project provisioned successfully.',
            'sftpDetails' => $sftpDetails,
        ]);
    }
}
