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

        $uploads = ClientUpload::orderBy('id', 'desc')->get()->map(function($upload) {
            $client = \App\Models\User::where('email', $upload->created_by_email)->first();
            $upload->client_sftp_user = $client ? ($client->sftp_username ?: \Illuminate\Support\Str::slug($client->name)) : 'guest';
            
            // 🚀 USER-HIDE INDICATOR (v211): If user hid it, let Admin know.
            if ($upload->request_status === 'user_hidden') {
                $upload->admin_note = "User removed this from their view.";
            }
            
            return $upload;
        });
        return response()->json($uploads);
    }

    public function getProcessingRequests()
    {
        $requests = ProcessingRequest::orderBy('id', 'desc')->get();
        return response()->json($requests);
    }

    public function getPathConfig()
    {
        // 🚀 FULL PATH SYNC (v168): Ensure we return the absolute system root
        return response()->json([
            'success' => true,
            'uploadRootAbsolute' => config('filesystems.disks.sftp_delivery.root', '/home/tiquan/'),
            'remoteBasePath' => config('filesystems.disks.sftp_delivery.root', '/home/tiquan/'),
            'sftpUsername' => config('filesystems.disks.sftp_delivery.username', 'tiquan'),
            'sftpPort' => env('SFTP_DELIVERY_PORT', 2222),
            'sftpHost' => config('filesystems.disks.sftp_delivery.host', '172.21.107.151'),
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

            // Ensure the delivered directory exists for manual SFTP placement
            try {
                $client = \App\Models\User::where('email', $upload->created_by_email)->first();
                $sftpUser = $client ? ($client->sftp_username ?: \Illuminate\Support\Str::slug($client->name)) : 'guest';
                // 🚀 NESTED ALIGNMENT (v221): Pre-create the standardized delivery folder
                $targetDir = "uploads/{$sftpUser}/{$upload->project_id}/delivered";
                Storage::disk('sftp_delivery')->makeDirectory($targetDir);
            } catch (\Exception $e) {
                \Log::warning("Could not pre-create delivered directory: " . $e->getMessage());
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
        $nitroDelivery = ($request->input('nitro_delivery') === true || $request->input('nitro_delivery') === 'true');

        // 🚀 HYPER-PROTECTION (v148): Prevent timeout during multi-GB transfers
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '-1'); 
        }
        set_time_limit(0);
        ignore_user_abort(true);

        \Log::info("📦 DELIVERY START: Project [{$upload->project_id}] Method [{$method}] Nitro [{$nitroDelivery}]");

        if ($hasUpload || $manualFileName || $gdriveLink || $nitroDelivery) {
            if ($nitroDelivery) {
                // 🚀 GIGA-NITRO TWO-STEP MERGE (v138)
                $uId = $request->input('upload_id');
                if (!$uId) {
                    return response()->json(['success' => false, 'message' => 'Critical Error: Missing Nitro Session ID.']);
                }

                $nitroRoot = config('filesystems.disks.nitro.root', 'C:/DataPortal_Nitro_Storage');
                // 🚀 NAMESPACE ALIGNMENT (v182): Look specifically in the delivery sub-folder
                $shardsDir = rtrim($nitroRoot . '/' . ($upload->project_id ?: ''), '/') . '/delivery_shards';
                $tempPath = storage_path('app/temp_nitro_' . $uId . '.zip');
                
                $out = fopen($tempPath, 'wb');
                stream_set_chunk_size($out, 2097152); // 2MB Buffer
                if (!$out) {
                    return response()->json(['success' => false, 'message' => 'Could not create local assembly buffer. Check disk space.']);
                }

                $i = 0;
                while (true) {
                    $slot = $shardsDir . '/' . $uId . '.slot' . $i;
                    if (file_exists($slot)) {
                        $in = fopen($slot, 'rb');
                        stream_set_chunk_size($in, 2097152);
                        stream_copy_to_stream($in, $out);
                        fclose($in);
                        @unlink($slot); // Cleanup shard
                        $i++;
                    } else {
                        break;
                    }
                }
                fclose($out);

                if ($i === 0) {
                    @unlink($tempPath);
                    return response()->json(['success' => false, 'message' => 'Nitro Error: No shards found for this delivery session.']);
                }

                // Final SFTP Destination
                $finalName = $upload->project_id . '-processed.zip';
                $client = \App\Models\User::where('email', $upload->created_by_email)->first();
                $sftpUser = $client ? ($client->sftp_username ?: \Illuminate\Support\Str::slug($client->name)) : 'guest';
                
                // 🚀 NESTED DELIVERY (v205): Place results INSIDE the project folder (project/delivered/) for perfect jail alignment.
                $deliveryPathRelative = "uploads/{$sftpUser}/{$upload->project_id}/delivered/{$finalName}";
                $root = rtrim(config('filesystems.disks.sftp_delivery.root', '/home/tiquan/'), '/');
                $deliveryPathAbsolute = "{$root}/{$deliveryPathRelative}";

                $disk = Storage::disk('sftp_delivery');
                $stream = fopen($tempPath, 'r');
                $success = $disk->put($deliveryPathRelative, $stream);
                fclose($stream);
                @unlink($tempPath);

                if (!$success) {
                    return response()->json(['success' => false, 'message' => 'SFTP Transfer failed. Destination may be unreachable.'], 500);
                }

                $upload->delivered_file_path = $deliveryPathAbsolute;
                $upload->delivery_method = 'portal';

            } elseif ($gdriveLink) {
                // Method C: Google Drive Link (v156: Use delivered_file_path to avoid overwriting user raw data link)
                $upload->delivery_method = 'google_drive';
                $upload->delivered_file_path = $gdriveLink;

            } elseif ($method === 'portal' || $method === 'sftp') {
                // 🚀 CLIENT-CENTRIC PATH (v139): Deliver into the user's own SFTP folder
                $client = \App\Models\User::where('email', $upload->created_by_email)->first();
                $sftpUser = $client ? ($client->sftp_username ?: \Illuminate\Support\Str::slug($client->name)) : 'guest';
                
                // Standardized delivery path
                $fileName = $hasUpload ? ($upload->project_id . '-processed.zip') : $manualFileName;
                // 🚀 NESTED DELIVERY (v194)
                $pathRelative = "uploads/{$sftpUser}/{$upload->project_id}/delivered/{$fileName}";
                $root = rtrim(config('filesystems.disks.sftp_delivery.root', '/home/tiquan/'), '/');
                $pathAbsolute = "{$root}/{$pathRelative}";
                
                if ($hasUpload) {
                    // Method A: Admin uploaded via Web Form (Large files should use Nitro)
                    $file = $request->file('delivered_file');
                    $success = Storage::disk('sftp_delivery')->put($pathRelative, fopen($file, 'r+'));
                    if (!$success) {
                         \Log::error("  - [FAIL] Standard Web Upload failed for: {$pathRelative}");
                         return response()->json(['success' => false, 'message' => 'Upload to storage failed. Please use Nitro Upload for large files.'], 500);
                    }
                } else {
                    // Method B: Admin already placed file via WinSCP
                    if (!Storage::disk('sftp_delivery')->exists($pathRelative)) {
                        return response()->json([
                            'success' => false, 
                            'message' => "File not found on SFTP at: {$pathRelative}. Did you remember to move it therapy via WinSCP?"
                        ], 404);
                    }
                }

                $upload->delivered_file_path = $pathAbsolute;
                if ($method === 'sftp') {
                    $upload->sftp_delivery_path = $pathAbsolute;
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

    /**
     * 🚀 PROACTIVE FOLDER PREP (v184): Creates the SFTP directory before Admin opens WinSCP.
     */
    public function ensureDeliveryFolder($id)
    {
        try {
            $upload = \App\Models\ClientUpload::findOrFail($id);
            $client = \App\Models\User::where('email', $upload->created_by_email)->first();
            $sftpUser = $client ? ($client->sftp_username ?: \Illuminate\Support\Str::slug($client->name)) : 'guest';
            $projId = $upload->project_id ?: 'unknown';
            
            $disk = Storage::disk('sftp_delivery');

            // 🚀 NESTED DELIVERY (v205): Use the path inside the project folder
            $dir = "uploads/{$sftpUser}/{$projId}/delivered";
            if (!$disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }

            $hint = "Port 2223: /home/tiquan/uploads/{$sftpUser}/{$projId}/delivered/";
            return response()->json(['success' => true, 'path' => $hint]);
        } catch (\Exception $e) {
            \Log::error("Folder Provision Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not auto-create folder: ' . $e->getMessage()]);
        }
    }

    public function getDeliveryPath($id)
    {
        $u = \App\Models\ClientUpload::findOrFail($id);
        $client = \App\Models\User::where('email', $u->created_by_email)->first();
        $sftpUser = $client ? ($client->sftp_username ?: \Str::slug($client->name)) : 'guest';
        $projId = $u->project_id ?: 'unknown';
        
        $hint = "Port 2223: /home/tiquan/uploads/{$sftpUser}/{$projId}/delivered/";
        return response()->json(['success' => true, 'path' => $hint]);
    }
}
