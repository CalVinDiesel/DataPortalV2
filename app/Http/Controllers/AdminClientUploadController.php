<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientUpload;
use App\Models\ProcessingRequest;
use Illuminate\Support\Facades\DB;

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
        $procReq = ProcessingRequest::find($id);
        if (!$procReq) {
            return response()->json(['success' => false, 'message' => 'Not found.']);
        }

        $procReq->status = 'completed';
        $procReq->delivered_at = now();
        $procReq->delivery_notes = $request->input('delivery_notes');
        $procReq->save();

        // Update ClientUpload status
        $upload = ClientUpload::find($procReq->upload_id);
        if ($upload) {
            $upload->request_status = 'completed';
            $upload->save();
        }

        return response()->json(['success' => true, 'message' => 'Marked as delivered.']);
    }
}
