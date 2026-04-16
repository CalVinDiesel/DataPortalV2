<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientUpload;
use App\Models\ProcessingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Mail\ProcessedDataDelivered;

class AdminClientUploadController extends Controller
{
    public function getUploads()
    {
        // Invisible Automation Trigger:
        // Automatically cleanup expired deliveries whenever an admin views the uploads.
        // We use a cache lock of 24 hours to ensure it doesn't run on every single refresh.
        Cache::remember('delivery_cleanup_lock', 86400, function () {
            Artisan::call('app:cleanup-expired-deliveries');
            return true;
        });

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
        // Use the actual root configured for the SFTP disk
        return response()->json([
            'success' => true,
            'uploadRootAbsolute' => config('filesystems.disks.sftp_delivery.root', '/home/tiquan/'),
            'remoteBasePath' => config('filesystems.disks.sftp_delivery.root', '/home/tiquan/'),
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

            // Ensure the deliveries directory exists for manual SFTP placement
            try {
                Storage::disk('sftp_delivery')->makeDirectory("deliveries/{$upload->project_id}");
            } catch (\Exception $e) {
                \Log::warning("Could not pre-create delivery directory: " . $e->getMessage());
            }

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

        $manualFileName = $request->input('manual_file_name');
        $gdriveLink = $request->input('google_drive_link');
        $hasUpload = $request->hasFile('delivered_file');

        if ($hasUpload || $manualFileName || $gdriveLink) {
            if ($gdriveLink) {
                // Method C: Google Drive Link
                $upload->delivery_method = 'google_drive';
                $upload->google_drive_link = $gdriveLink;
            } elseif ($method === 'portal' || $method === 'sftp') {
                // Standardized delivery path
                $fileName = $hasUpload ? ($upload->project_id . '-processed.zip') : $manualFileName;
                $path = "deliveries/{$upload->project_id}/{$fileName}";
                
                if ($hasUpload) {
                    // Method A: Admin uploaded via Web Form
                    $file = $request->file('delivered_file');
                    Storage::disk('sftp_delivery')->put($path, fopen($file, 'r+'));
                } else {
                    // Method B: Admin already placed file via WinSCP
                    if (!Storage::disk('sftp_delivery')->exists($path)) {
                        return response()->json([
                            'success' => false, 
                            'message' => "File not found on SFTP at: {$path}. Did you remember to move it therapy via WinSCP?"
                        ], 404);
                    }
                }

                $upload->delivered_file_path = $path;
                if ($method === 'sftp') {
                    $upload->sftp_delivery_path = $path;
                }
            } elseif ($method === 'google_drive' && $hasUpload) {
                // Google Drive currently only supports direct web upload in this simple implementation
                $file = $request->file('delivered_file');
                $path = Storage::disk('google_drive')->put($fileName, fopen($file, 'r+'));
                $upload->delivered_file_path = $path;
                $upload->gdrive_delivery_folder_id = config('filesystems.disks.google_drive.folderId');
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Please either upload a file or provide an existing SFTP filename.'], 400);
        }

        $upload->request_status = 'completed';
        $upload->delivered_at = now();
        $upload->delivered_expires_at = now()->addDays(7);
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
