<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientUpload;
use App\Models\ProcessingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProcessedDataDelivered;

class AdminClientUploadController extends Controller
{
    public function getUploads()
    {
        $uploads = ClientUpload::orderBy('id', 'desc')->get();
        return response()->json($uploads);
    }

    public function getProcessingRequests()
    {
        $requests = ProcessingRequest::orderBy('id', 'desc')->get();
        return response()->json($requests);
    }

    public function getPathConfig()
    {
        // Dummy config for SFTP paths
        return response()->json([
            'success' => true,
            'uploadRootAbsolute' => env('SFTP_UPLOAD_ROOT', '/sftp/uploads'),
            'remoteBasePath' => env('SFTP_REMOTE_BASE', '/home/sftpuser'),
        ]);
    }

    public function submitDecision(Request $request, $id)
    {
        $upload = ClientUpload::find($id);
        if (!$upload) {
            return response()->json(['success' => false, 'message' => 'Upload record not found.']);
        }

        $action = $request->input('action');
        $reason = $request->input('reason');

        if ($action === 'accept') {
            $upload->request_status = 'review';
            $upload->decided_at = now();
            // TODO: send email to client
        } elseif ($action === 'processing') {
            $upload->request_status = 'processing';

            // Create a processing request record
            ProcessingRequest::create([
                'upload_id' => $upload->id,
                'status' => 'processing',
                'requested_at' => now()
            ]);
        } elseif ($action === 'reject') {
            $upload->request_status = 'rejected';
            $upload->rejected_reason = $reason;
            $upload->decided_at = now();
            // TODO: send email to client
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid action.']);
        }

        $upload->save();

        return response()->json(['success' => true, 'message' => 'Decision recorded.']);
    }

    public function deleteUpload($id)
    {
        $upload = ClientUpload::find($id);
        if (!$upload) {
            return response()->json(['success' => false, 'message' => 'Not found.']);
        }

        // Delete connected processing requests first
        ProcessingRequest::where('upload_id', $id)->delete();
        $upload->delete();

        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }

    public function markDelivered(Request $request, $id)
    {
        $procReq = ProcessingRequest::findOrFail($id);
        $upload = ClientUpload::findOrFail($procReq->upload_id);

        $method = $request->input('delivery_method', $upload->delivery_method ?: 'portal');
        $upload->delivery_method = $method;

        // Note: For SFTP delivery, we might just be marking as delivered if files were uploaded manually
        // But if a file is provided in the request, we push it to the destination.
        if ($request->hasFile('delivered_file')) {
            $file = $request->file('delivered_file');
            $fileName = $upload->project_id . '-processed.zip';

            if ($method === 'portal' || $method === 'sftp') {
                // Both use the sftp_delivery disk, but portals is hidden in /projects/
                // while 'sftp' might use a specific user path if we had one.
                // For now, we follow the plan: /projects/{id}/processed/ for both.
                $path = "projects/{$upload->project_id}/processed/{$fileName}";
                
                // Use put() which handles streams efficiently
                Storage::disk('sftp_delivery')->put($path, fopen($file, 'r+'));
                $upload->delivered_file_path = $path;
                
                if ($method === 'sftp') {
                    $upload->sftp_delivery_path = $path;
                }
            } elseif ($method === 'google_drive') {
                // the folderId is from config/env, or maybe we want a dynamic one?
                // Plan says: use Admin Service Account and shared folder.
                $path = Storage::disk('google_drive')->put($fileName, fopen($file, 'r+'));
                $upload->delivered_file_path = $path;
                // Get the real GDrive ID if possible? (adapter dependent)
                $upload->gdrive_delivery_folder_id = config('filesystems.disks.google_drive.folderId');
            }
        }

        $upload->request_status = 'completed';
        $upload->delivered_at = now();
        $upload->save();

        $procReq->status = 'completed';
        $procReq->delivered_at = now();
        $procReq->delivery_notes = $request->input('delivery_notes');
        $procReq->save();

        // Notify Client
        try {
            Mail::to($upload->created_by_email)->send(new ProcessedDataDelivered($upload));
        } catch (\Exception $e) {
            // Log mail error but don't fail the delivery mark
            \Log::error("Failed to send delivery email: " . $e->getMessage());
        }

        return response()->json([
            'success' => true, 
            'message' => 'Project marked as delivered via ' . $method . ' and client notified.',
            'upload' => $upload
        ]);
    }
}
