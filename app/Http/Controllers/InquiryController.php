<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\MapData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\InquiryReceived;
use App\Mail\AdminNewInquiryAlert;
use App\Mail\InquirySentToUser;
use App\Mail\TilesReadyNotification;

class InquiryController extends Controller
{
    /**
     * Show the form for creating a new inquiry.
     */
    public function create()
    {
        do {
            $inquiryId = 'INQ-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Inquiry::where('inquiry_id', $inquiryId)->exists());

        $mapLocations = MapData::orderBy('title', 'asc')->get();

        $mapLocations->transform(function ($item) {
            $item->thumbNailUrl = $this->rewriteThumbnailUrl($item->thumbNailUrl, $item->mapDataID);
            return $item;
        });

        return view('portal.inquiry-new', compact('inquiryId', 'mapLocations'));
    }

    /**
     * Store a newly created inquiry in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'inquiry_id'        => 'required|string|unique:inquiries,inquiry_id',
            'map_data_id'       => 'required|string|exists:map_data,mapDataID',
            'output_categories' => 'required|array',
            'output_categories.*' => 'string',
            'area_coordinates'  => 'required|array',
        ]);

        $user = Auth::user();

        $inquiry = Inquiry::create([
            'inquiry_id'        => $request->inquiry_id,
            'user_id'           => $user->id,
            'user_email'        => $user->email,
            'map_data_id'       => $request->map_data_id,
            'output_categories' => $request->output_categories,
            'area_coordinates'  => $request->area_coordinates,
            'status'            => 'pending',
        ]);

        // Auto-create the SFTP delivery folder as soon as the inquiry is created
        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('sftp_delivery');
            $relativePath = $inquiry->getSftpDeliveryRelativePath();
            if (!$disk->exists($relativePath)) {
                $disk->makeDirectory($relativePath);
            }
        } catch (\Exception $sftpEx) {
            \Illuminate\Support\Facades\Log::warning('Could not auto-create SFTP delivery directory during inquiry creation: ' . $sftpEx->getMessage());
        }

        // Load relations for mail
        $inquiry->load(['user', 'mapData']);

        // Send confirmation to user
        try {
            Mail::to($user->email)->send(new InquiryReceived($inquiry));
        } catch (\Exception $e) {
            Log::error('InquiryReceived mail failed', [
                'inquiry_id' => $inquiry->inquiry_id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Alert admins and superadmins
        try {
            $adminEmails = \App\Models\User::getAdminEmails();
            foreach ($adminEmails as $adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new AdminNewInquiryAlert($inquiry));
                } catch (\Exception $e) {
                    Log::error('AdminNewInquiryAlert mail failed for ' . $adminEmail, [
                        'inquiry_id' => $inquiry->inquiry_id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('AdminNewInquiryAlert dispatching failed', [
                'inquiry_id' => $inquiry->inquiry_id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inquiry request sent successfully.',
            'data'    => $inquiry,
        ]);
    }

    /**
     * Display a listing of the user's own inquiries.
     */
    public function my()
    {
        $inquiries = Inquiry::with(['mapData'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Auto-check and heal delivery_ready status for any completed inquiries
        foreach ($inquiries as $inquiry) {
            if ($inquiry->status === 'completed' && !$inquiry->delivery_ready) {
                $this->autoCheckAndMarkDeliveryReady($inquiry);
            }
        }

        return view('portal.inquiry-my', compact('inquiries'));
    }

    /**
     * Show the form for editing an existing inquiry.
     */
    public function edit($id)
    {
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($inquiry->status, ['pending', 'reviewed', 'rejected'])) {
            return redirect()->route('inquiry.my')->with('error', 'You can only edit inquiries that are pending review, under review, or rejected.');
        }

        $mapLocations = MapData::orderBy('title', 'asc')->get();

        $mapLocations->transform(function ($item) {
            $item->thumbNailUrl = $this->rewriteThumbnailUrl($item->thumbNailUrl, $item->mapDataID);
            return $item;
        });

        return view('portal.inquiry-edit', compact('inquiry', 'mapLocations'));
    }

    /**
     * Update the specified inquiry in storage.
     */
    public function update(Request $request, $id)
    {
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($inquiry->status, ['pending', 'reviewed', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit inquiries that are pending review, under review, or rejected.',
            ], 422);
        }

        $request->validate([
            'map_data_id'       => 'required|string|exists:map_data,mapDataID',
            'output_categories' => 'required|array',
            'output_categories.*' => 'string',
            'area_coordinates'  => 'required|array',
        ]);

        $inquiry->update([
            'map_data_id'       => $request->map_data_id,
            'output_categories' => $request->output_categories,
            'area_coordinates'  => $request->area_coordinates,
            'status'            => 'pending', // Reset status to pending so admin re-reviews it
            'rejection_reason'  => null,      // Clear any prior rejection reason
        ]);

        // Load relations for mail
        $inquiry->load(['user', 'mapData']);

        // Send confirmation of update to user
        try {
            Mail::to($inquiry->user_email)->send(new InquiryReceived($inquiry, true));
        } catch (\Exception $e) {
            Log::error('InquiryReceived update mail failed', [
                'inquiry_id' => $inquiry->inquiry_id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Alert admins and superadmins about update
        try {
            $adminEmails = \App\Models\User::getAdminEmails();
            foreach ($adminEmails as $adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new AdminNewInquiryAlert($inquiry, true));
                } catch (\Exception $e) {
                    Log::error('AdminNewInquiryAlert update mail failed for ' . $adminEmail, [
                        'inquiry_id' => $inquiry->inquiry_id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('AdminNewInquiryAlert update dispatching failed', [
                'inquiry_id' => $inquiry->inquiry_id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inquiry request updated successfully.',
            'data'    => $inquiry,
        ]);
    }

    /**
     * Client: delete their own inquiry request.
     */
    public function destroy($id)
    {
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($inquiry->status, ['pending', 'reviewed', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete inquiries that are pending review, under review, or rejected.',
            ], 422);
        }

        $relativePath = $inquiry->getSftpDeliveryRelativePath();
        $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();

        try {
            if (is_dir($absolutePath)) {
                $this->deleteLocalDirRecursive($absolutePath);
                Log::info("destroy (client): Deleted local folder: " . $absolutePath);
            }
        } catch (\Exception $e) {
            Log::warning("destroy (client): Failed to delete local folder: " . $e->getMessage());
        }

        try {
            $disk = Storage::disk('sftp_delivery');
            if ($disk->exists($relativePath)) {
                $disk->deleteDirectory($relativePath);
                Log::info("destroy (client): Deleted SFTP folder: " . $relativePath);
            }
        } catch (\Exception $e) {
            Log::warning("destroy (client): Failed to delete SFTP folder: " . $relativePath);
        }

        $inquiryIdStr = $inquiry->inquiry_id;
        $inquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry request ' . $inquiryIdStr . ' deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Admin: show the manage inquiries page.
     */
    public function adminIndex()
    {
        return view('admin.manage-inquiries');
    }

    /**
     * Admin: return all inquiries as JSON (optionally filtered by status).
     */
    public function adminList(Request $request)
    {
        $query = Inquiry::with(['user', 'mapData'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inquiry_id', 'ilike', "%{$search}%")
                  ->orWhere('user_email', 'ilike', "%{$search}%");
            });
        }

        $inquiries = $query->get()->map(function ($i) {
            return $this->formatInquiryForApi($i);
        });

        return response()->json($inquiries);
    }

    /**
     * Admin: return a single inquiry as JSON.
     */
    public function adminShow($id)
    {
        $inquiry = Inquiry::with(['user', 'mapData'])->findOrFail($id);
        return response()->json($this->formatInquiryForApi($inquiry));
    }

    /**
     * Admin: update inquiry status and optional fields.
     */
    public function adminUpdateStatus(Request $request, $id)
    {
        $inquiry = Inquiry::with(['user', 'mapData'])->findOrFail($id);

        $request->validate([
            'status'           => 'required|in:' . implode(',', Inquiry::STATUSES),
            'admin_notes'      => 'nullable|string|max:2000',
            'rejection_reason' => 'nullable|string|max:2000',
            'quotation_pdf'    => 'nullable|file|mimes:pdf|max:20480', // max 20MB
        ]);

        $newStatus = $request->status;
        $oldStatus = $inquiry->status;

        // Enforce payment receipt exists before admin can move to processing or completed status (Bypassed for FOC pre-launch)
        /*
        if (in_array($newStatus, ['processing', 'completed']) && !$inquiry->payment_receipt_path) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update status. The client has not uploaded a payment receipt yet.',
            ], 422);
        }
        */

        $notes = $inquiry->admin_notes;
        if (!is_array($notes)) {
            $notes = [];
        }

        if ($request->has('admin_notes')) {
            $notes[$newStatus] = $request->admin_notes;
        }

        $updateData = [
            'status'           => $newStatus,
            'admin_notes'      => $notes,
            'rejection_reason' => $request->rejection_reason,
        ];

        // Handle quotation PDF upload (only relevant when status = quoted)
        if ($request->hasFile('quotation_pdf') && $request->file('quotation_pdf')->isValid()) {
            // Delete old PDF if it exists
            if ($inquiry->quotation_pdf_path && Storage::disk('local')->exists($inquiry->quotation_pdf_path)) {
                Storage::disk('local')->delete($inquiry->quotation_pdf_path);
            }
            $file = $request->file('quotation_pdf');
            $filename = 'quotation_' . $inquiry->inquiry_id . '_' . time() . '.pdf';
            $path = $file->storeAs('quotation_pdfs/' . $inquiry->inquiry_id, $filename, 'local');
            $updateData['quotation_pdf_path'] = $path;
        }

        // Stamp timestamps on key transitions
        if ($newStatus === 'quoted' && $oldStatus !== 'quoted') {
            $updateData['quoted_at'] = now();
        }
        if ($newStatus === 'processing' && $oldStatus !== 'processing') {
            $updateData['processing_started_at'] = now();

            // Proactively create the delivery directory (local-first, fallback to SFTP)
            try {
                $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();
                if (!is_dir($absolutePath)) {
                    if (!@mkdir($absolutePath, 0777, true)) {
                        $disk = Storage::disk('sftp_delivery');
                        $dir = $inquiry->getSftpDeliveryRelativePath();
                        if (!$disk->exists($dir)) {
                            $disk->makeDirectory($dir);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not pre-create directory for processing Inquiry [{$inquiry->inquiry_id}]: " . $e->getMessage());
            }
        }
        // When transitioning INTO completed, stamp the delivered_at timestamp
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $updateData['delivered_at'] = now();

            // Ensure directory exists (local-first, fallback to SFTP)
            try {
                $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();
                if (!is_dir($absolutePath)) {
                    if (!@mkdir($absolutePath, 0777, true)) {
                        $disk = Storage::disk('sftp_delivery');
                        $dir = $inquiry->getSftpDeliveryRelativePath();
                        if (!$disk->exists($dir)) {
                            $disk->makeDirectory($dir);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not pre-create directory for completed Inquiry [{$inquiry->inquiry_id}]: " . $e->getMessage());
            }
        }

        $inquiry->update($updateData);
        $inquiry->refresh()->load(['user', 'mapData']);

        // Auto-check if delivery files exist immediately when status is updated to completed
        if ($inquiry->status === 'completed' && !$inquiry->delivery_ready) {
            $this->autoCheckAndMarkDeliveryReady($inquiry);
        }

        // Send email to user when admin sends a formal quotation
        $emailSent = false;
        $sendEmail = $request->boolean('send_email', false);

        if ($newStatus === 'quoted' && ($sendEmail || $oldStatus !== 'quoted')) {
            if ($oldStatus !== 'quoted' || $sendEmail) {
                try {
                    Mail::to($inquiry->user_email)->send(new InquirySentToUser($inquiry));
                    $emailSent = true;
                } catch (\Exception $e) {
                    Log::error('InquirySentToUser mail failed', [
                        'inquiry_id' => $inquiry->inquiry_id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Inquiry updated successfully.' . ($emailSent ? ' Quotation email sent to client.' : ''),
            'email_sent' => $emailSent,
            'data'       => $this->formatInquiryForApi($inquiry),
        ]);
    }

    /**
     * Admin: stream/download the uploaded quotation PDF.
     */
    public function adminStreamQuotationPdf($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        if (!$inquiry->quotation_pdf_path || !Storage::disk('local')->exists($inquiry->quotation_pdf_path)) {
            abort(404, 'Quotation PDF not found.');
        }

        $filename = 'Quotation_' . $inquiry->inquiry_id . '.pdf';
        return Storage::disk('local')->download($inquiry->quotation_pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Client: download the formal quotation PDF for their own inquiry.
     */
    public function clientDownloadQuotationPdf(Request $request, $id)
    {
        $user = Auth::user();
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$inquiry->quotation_pdf_path || !Storage::disk('local')->exists($inquiry->quotation_pdf_path)) {
            abort(404, 'Quotation PDF not available yet.');
        }

        $filename = 'Quotation_' . $inquiry->inquiry_id . '.pdf';
        return Storage::disk('local')->download($inquiry->quotation_pdf_path, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Client: upload a payment receipt (image or PDF).
     */
    public function clientUploadReceipt(Request $request, $id)
    {
        $user = Auth::user();
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Enforce status is awaiting_payment
        if ($inquiry->status !== 'awaiting_payment') {
            return response()->json([
                'success' => false,
                'message' => 'Receipts can only be uploaded when status is Awaiting Payment.',
            ], 422);
        }

        $request->validate([
            'payment_receipt' => 'required|file|mimes:pdf,jpeg,png,jpg|max:20480', // max 20MB
        ]);

        if ($request->hasFile('payment_receipt') && $request->file('payment_receipt')->isValid()) {
            // Delete old receipt if it exists
            if ($inquiry->payment_receipt_path && Storage::disk('local')->exists($inquiry->payment_receipt_path)) {
                Storage::disk('local')->delete($inquiry->payment_receipt_path);
            }

            $file = $request->file('payment_receipt');
            $extension = $file->getClientOriginalExtension();
            $filename = 'receipt_' . $inquiry->inquiry_id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('payment_receipts/' . $inquiry->inquiry_id, $filename, 'local');

            $inquiry->update([
                'payment_receipt_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment receipt uploaded successfully.',
                'data'    => $this->formatInquiryForApi($inquiry->fresh(['user', 'mapData'])),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid file uploaded.',
        ], 400);
    }

    /**
     * Client: download/stream their own uploaded payment receipt.
     */
    public function clientDownloadPaymentReceipt($id)
    {
        $user = Auth::user();
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$inquiry->payment_receipt_path || !Storage::disk('local')->exists($inquiry->payment_receipt_path)) {
            abort(404, 'Payment receipt not found.');
        }

        $extension = pathinfo($inquiry->payment_receipt_path, PATHINFO_EXTENSION);
        $filename = 'Receipt_' . $inquiry->inquiry_id . '.' . $extension;

        $mime = 'application/octet-stream';
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            $mime = 'image/' . (strtolower($extension) === 'jpg' ? 'jpeg' : strtolower($extension));
        } elseif (strtolower($extension) === 'pdf') {
            $mime = 'application/pdf';
        }

        return Storage::disk('local')->download($inquiry->payment_receipt_path, $filename, [
            'Content-Type' => $mime,
        ]);
    }

    /**
     * Admin: download/stream the client's uploaded payment receipt.
     */
    public function adminStreamPaymentReceipt($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        if (!$inquiry->payment_receipt_path || !Storage::disk('local')->exists($inquiry->payment_receipt_path)) {
            abort(404, 'Payment receipt not found.');
        }

        $extension = pathinfo($inquiry->payment_receipt_path, PATHINFO_EXTENSION);
        $filename = 'Receipt_' . $inquiry->inquiry_id . '.' . $extension;

        $mime = 'application/octet-stream';
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            $mime = 'image/' . (strtolower($extension) === 'jpg' ? 'jpeg' : strtolower($extension));
        } elseif (strtolower($extension) === 'pdf') {
            $mime = 'application/pdf';
        }

        return Storage::disk('local')->download($inquiry->payment_receipt_path, $filename, [
            'Content-Type' => $mime,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELIVERY MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Admin: toggle the delivery_ready flag on a completed inquiry.
     * Before marking ready, verifies at least one file exists in the SFTP delivery folder.
     */
    public function adminToggleDelivery(Request $request, $id)
    {
        $inquiry = Inquiry::with(['user', 'mapData'])->findOrFail($id);

        $request->validate([
            'delivery_ready' => 'required|boolean',
        ]);

        $markReady = (bool) $request->delivery_ready;

        if ($markReady) {
            try {
                $relativePath = $inquiry->getSftpDeliveryRelativePath();
                $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();
                $exists = false;
                $hasTempFiles = false;

                if (is_dir($absolutePath)) {
                    $res = $this->checkDirectoryFilesRecursive($absolutePath);
                    $exists = $res['has_files'] && !$res['has_temp_files'];
                    $hasTempFiles = $res['has_temp_files'];
                } else {
                    @mkdir($absolutePath, 0777, true);

                    $disk = Storage::disk('sftp_delivery');
                    if (!$disk->exists($relativePath)) {
                        $disk->makeDirectory($relativePath);
                    }
                    try {
                        $contents = $disk->listContents($relativePath, true)->toArray();
                        $hasFiles = false;
                        $hasTemp = false;
                        foreach ($contents as $item) {
                            if ($item['type'] === 'file') {
                                $hasFiles = true;
                                $filename = basename($item['path']);
                                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                if ($ext === 'filepart' || $ext === 'tmp' || $ext === 'temp' || str_ends_with(strtolower($filename), '.filepart')) {
                                    $hasTemp = true;
                                }
                            }
                        }
                        $exists = $hasFiles && !$hasTemp;
                        $hasTempFiles = $hasTemp;
                    } catch (\Exception $e) {
                        $exists = false;
                    }
                }

                if ($hasTempFiles) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The 3D model upload is still in progress (temporary files detected). Please wait for WinSCP upload to complete before marking as ready.',
                    ], 422);
                }

                if (!$exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No files found in the delivery folder. Please upload the 3D model tiles via WinSCP first. Path: ' . $inquiry->getSftpDeliveryAbsolutePath(),
                    ], 422);
                }
            } catch (\Exception $e) {
                Log::warning('adminToggleDelivery: Could not verify files for ' . $inquiry->inquiry_id . ': ' . $e->getMessage());
            }
        }

        $updateData = ['delivery_ready' => $markReady];
        if ($markReady && !$inquiry->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        $inquiry->update($updateData);
        $inquiry->refresh()->load(['user', 'mapData']);

        if ($markReady) {
            try {
                $recipient = $inquiry->user_email;
                Mail::to($recipient)->send(new TilesReadyNotification($inquiry));
                Log::info('TilesReadyNotification sent to ' . $recipient . ' for ' . $inquiry->inquiry_id);
            } catch (\Exception $e) {
                Log::error('Failed to send TilesReadyNotification for ' . $inquiry->inquiry_id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => $markReady ? 'Delivery marked as ready. Client has been notified by email.' : 'Delivery marked as not ready. Client download disabled.',
            'data'    => $this->formatInquiryForApi($inquiry),
        ]);
    }

    /**
     * Recursively check if a directory has any files, and ensure none of them are temporary (.filepart, .tmp).
     */
    protected function checkDirectoryFilesRecursive($dir): array
    {
        $hasFiles = false;
        $hasTempFiles = false;

        if (!is_dir($dir)) {
            return ['has_files' => false, 'has_temp_files' => false];
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $sub = $this->checkDirectoryFilesRecursive($path);
                if ($sub['has_files']) {
                    $hasFiles = true;
                }
                if ($sub['has_temp_files']) {
                    $hasTempFiles = true;
                }
            } else {
                $hasFiles = true;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext === 'filepart' || $ext === 'tmp' || $ext === 'temp' || str_ends_with(strtolower($file), '.filepart')) {
                    $hasTempFiles = true;
                }
            }
        }

        return ['has_files' => $hasFiles, 'has_temp_files' => $hasTempFiles];
    }

    /**
     * Admin: check what files exist on SFTP for a given inquiry's delivery folder.
     */
    public function adminCheckDelivery($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        $relativePath  = $inquiry->getSftpDeliveryRelativePath();
        $absolutePath  = $inquiry->getSftpDeliveryAbsolutePath();

        try {
            $files = [];
            $totalSize = 0;

            $disk = Storage::disk('sftp_delivery');
            if (!$disk->exists($relativePath)) {
                $disk->makeDirectory($relativePath);
            }
            $contents = $disk->listContents($relativePath, true)->toArray();

            foreach ($contents as $item) {
                if ($item->isFile()) {
                    $size = 0;
                    try { $size = $disk->size($item->path()); } catch (\Exception $e) {}
                    $files[] = [
                        'name' => basename($item->path()),
                        'path' => $item->path(),
                        'size' => $size,
                        'size_human' => $this->formatBytes($size),
                    ];
                    $totalSize += $size;
                }
            }

            return response()->json([
                'success'          => true,
                'sftp_path'        => '/' . $relativePath,
                'file_count'       => count($files),
                'total_size'       => $totalSize,
                'total_size_human' => $this->formatBytes($totalSize),
                'files'            => $files,
            ]);

        } catch (\Exception $e) {
            Log::error('adminCheckDelivery failed for ' . $inquiry->inquiry_id . ': ' . $e->getMessage());
            return response()->json([
                'success'   => false,
                'sftp_path' => '/' . $relativePath,
                'message'   => 'Could not read delivery folder: ' . $e->getMessage(),
                'files'     => [],
                'file_count'=> 0,
            ]);
        }
    }

    /**
     * Admin: cleanly delete an inquiry.
     */
    public function adminDestroy($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        $relativePath = $inquiry->getSftpDeliveryRelativePath();
        $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();

        try {
            if (is_dir($absolutePath)) {
                $this->deleteLocalDirRecursive($absolutePath);
                Log::info("adminDestroy: Deleted local folder: " . $absolutePath);
            }
        } catch (\Exception $e) {
            Log::warning("adminDestroy: Failed to delete local folder: " . $e->getMessage());
        }

        try {
            $disk = Storage::disk('sftp_delivery');
            if ($disk->exists($relativePath)) {
                $disk->deleteDirectory($relativePath);
                Log::info("adminDestroy: Deleted SFTP folder: " . $relativePath);
            }
        } catch (\Exception $e) {
            Log::warning("adminDestroy: Failed to delete SFTP folder: " . $e->getMessage());
        }

        $inquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry and all associated files deleted successfully.',
        ]);
    }

    /**
     * Helper to recursively delete a local directory.
     */
    private function deleteLocalDirRecursive($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->deleteLocalDirRecursive($filePath);
            } else {
                @unlink($filePath);
            }
        }
        @rmdir($dir);
    }

    /**
     * Client: accept the 3D model download disclaimer.
     */
    public function acceptDisclaimer(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $inquiry->update([
            'disclaimer_accepted_at' => now(),
            'disclaimer_ip_address'   => $request->ip(),
            'disclaimer_user_agent'   => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Client: download the 3D model tiles for their completed inquiry.
     */
    public function clientDownload($id)
    {
        $user      = Auth::user();
        $inquiry   = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$inquiry->disclaimer_accepted_at) {
            return response()->json(['error' => 'You must accept the disclaimer before downloading.'], 403);
        }

        if ($inquiry->status !== 'completed') {
            return response()->json(['error' => 'Your 3D model is not yet ready for download.'], 403);
        }

        if (!$inquiry->delivery_ready) {
            return response()->json(['error' => 'Your 3D model tiles are still being prepared. Please check back soon.'], 403);
        }

        session_write_close();
        ignore_user_abort(true);
        if (function_exists('ini_set')) { ini_set('memory_limit', '-1'); }
        set_time_limit(0);

        $relativePath = $inquiry->getSftpDeliveryRelativePath();
        $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();
        $disk         = Storage::disk('sftp_delivery');
        $zipName      = $inquiry->inquiry_id . '_3d_tiles.zip';
        $safeZipName  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $zipName);

        if (file_exists($absolutePath) && is_readable($absolutePath)) {
            return $this->streamLocalDelivery($absolutePath, $safeZipName);
        }

        try {
            $contents = $disk->listContents($relativePath, true)->toArray();

            if (empty($contents)) {
                return response()->json(['error' => 'No delivery files found. Please contact support.'], 404);
            }

            $fileItems = array_filter($contents, fn($c) => $c->isFile());
            if (count($fileItems) === 1) {
                $singleItem = reset($fileItems);
                $singlePath = $singleItem->path();
                $origName   = basename($singlePath);
                $ext        = pathinfo($origName, PATHINFO_EXTENSION);
                $downloadName = $ext && strtolower($ext) !== 'zip'
                    ? preg_replace('/\.zip$/i', '.' . $ext, $safeZipName)
                    : $safeZipName;
                $size       = null;
                try { $size = $disk->size($singlePath); } catch (\Exception $e) {}
                $mime = Str::endsWith(strtolower($origName), '.zip') ? 'application/zip' : 'application/octet-stream';

                return response()->stream(function () use ($disk, $singlePath) {
                    while (ob_get_level() > 0) ob_end_clean();
                    $stream = $disk->readStream($singlePath);
                    if ($stream) {
                        while (!feof($stream)) { echo fread($stream, 4194304); flush(); }
                        fclose($stream);
                    }
                }, 200, [
                    'Content-Type'        => $mime,
                    'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                    'Content-Length'      => $size,
                    'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                    'Pragma'              => 'no-cache',
                    'Expires'             => '0',
                    'X-Accel-Buffering'   => 'no',
                ]);
            }

            $tempDir = storage_path('app/temp_downloads/' . uniqid('inq_dl_'));
            mkdir($tempDir, 0777, true);

            try {
                foreach ($contents as $item) {
                    $rel = Str::after($item->path(), $relativePath . '/');
                    if ($item->isDir()) {
                        @mkdir($tempDir . '/' . $rel, 0777, true);
                    } else {
                        $localFilePath = $tempDir . '/' . $rel;
                        @mkdir(dirname($localFilePath), 0777, true);
                        $stream = $disk->readStream($item->path());
                        if ($stream) { file_put_contents($localFilePath, $stream); fclose($stream); }
                    }
                }

                $tempZipPath = storage_path('app/temp_downloads/' . uniqid('dl_') . '.zip');
                $zip         = new \ZipArchive();
                if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    throw new \Exception('Could not create zip archive.');
                }
                $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempDir), \RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($iter as $f) {
                    if (!$f->isDir()) {
                        $zip->addFile($f->getRealPath(), substr($f->getRealPath(), strlen($tempDir) + 1));
                    }
                }
                $zip->close();

            } finally {
                $deleteFolder = function ($dir) use (&$deleteFolder) {
                    if (!file_exists($dir)) return;
                    foreach (array_diff(scandir($dir), ['.', '..']) as $f) {
                        is_dir("$dir/$f") ? $deleteFolder("$dir/$f") : @unlink("$dir/$f");
                    }
                    @rmdir($dir);
                };
                $deleteFolder($tempDir);
            }

            return response()->download($tempZipPath, $safeZipName, [
                'Content-Type'  => 'application/zip',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma'        => 'no-cache',
                'Expires'       => '0',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('clientDownload failed for inquiry ' . $inquiry->inquiry_id . ': ' . $e->getMessage());
            return response()->json(['error' => 'Download failed. Please try again or contact support.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Stream a delivery from a local absolute filesystem path (file or directory).
     */
    private function streamLocalDelivery(string $absolutePath, string $zipName)
    {
        if (is_file($absolutePath)) {
            $origName  = basename($absolutePath);
            $safeName  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
            $mime      = Str::endsWith(strtolower($origName), '.zip') ? 'application/zip' : 'application/octet-stream';

            return response()->download($absolutePath, $safeName, [
                'Content-Type'  => $mime,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        // If it is a directory, check if it contains exactly one file to prevent double-zipping
        $files = [];
        if (is_dir($absolutePath)) {
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolutePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iter as $f) {
                if ($f->isFile()) {
                    $files[] = $f->getRealPath();
                }
            }
        }

        if (count($files) === 1) {
            $singleFile = $files[0];
            $origName   = basename($singleFile);
            $ext        = pathinfo($origName, PATHINFO_EXTENSION);
            $downloadName = $ext && strtolower($ext) !== 'zip'
                ? preg_replace('/\.zip$/i', '.' . $ext, $zipName)
                : $zipName;
            $mime = Str::endsWith(strtolower($origName), '.zip') ? 'application/zip' : 'application/octet-stream';

            return response()->download($singleFile, $downloadName, [
                'Content-Type'  => $mime,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        $tempZipPath = storage_path('app/temp_downloads/' . uniqid('dl_') . '.zip');
        @mkdir(dirname($tempZipPath), 0777, true);
        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($absolutePath), \RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($iter as $f) {
                if (!$f->isDir()) {
                    $zip->addFile($f->getRealPath(), substr($f->getRealPath(), strlen($absolutePath) + 1));
                }
            }
            $zip->close();
        }
        return response()->download($tempZipPath, $zipName, [
            'Content-Type'  => 'application/zip',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Format bytes into human-readable size string.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = $bytes > 0 ? floor(log($bytes) / log(1024)) : 0;
        $pow   = min($pow, count($units) - 1);
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * Format an inquiry model as an array for API responses.
     */
    private function formatInquiryForApi(Inquiry $q): array
    {
        return [
            'id'                       => $q->id,
            'inquiry_id'               => $q->inquiry_id,
            'user_id'                  => $q->user_id,
            'user_email'               => $q->user_email,
            'user_name'                => $q->user->name ?? '—',
            'map_data_id'              => $q->map_data_id,
            'map_title'                => $q->mapData->title ?? $q->map_data_id,
            'map_3d_tiles'             => $q->mapData->{'3dTiles'} ?? null,
            'map_x_axis'               => $q->mapData->xAxis ?? null,
            'map_y_axis'               => $q->mapData->yAxis ?? null,
            'output_categories'        => $q->output_categories,
            'area_coordinates'         => $q->area_coordinates,
            'status'                   => $q->status,
            'status_label'             => $q->status_label,
            'status_color'             => $q->status_color,
            'admin_notes'              => $q->admin_notes,
            'rejection_reason'         => $q->rejection_reason,
            'quoted_price'             => $q->quoted_price,
            'quoted_at'                => $q->quoted_at?->format('d M Y, h:i A'),
            'quotation_pdf_path'       => $q->quotation_pdf_path,
            'quotation_pdf_url'        => $q->quotation_pdf_path
                ? url('/api/admin/inquiries/' . $q->id . '/quotation-pdf')
                : null,
            'quotation_pdf_client_url' => $q->quotation_pdf_path
                ? url('/api/inquiry/' . $q->id . '/quotation-pdf')
                : null,
            'payment_receipt_path'     => $q->payment_receipt_path,
            'payment_receipt_url'      => $q->payment_receipt_path
                ? url('/api/admin/inquiries/' . $q->id . '/payment-receipt')
                : null,
            'payment_receipt_client_url' => $q->payment_receipt_path
                ? url('/api/inquiry/' . $q->id . '/payment-receipt')
                : null,
            'bank_name'                => $q->bank_name,
            'bank_account_number'      => $q->bank_account_number,
            'bank_account_name'        => $q->bank_account_name,
            'payment_deadline'         => $q->payment_deadline?->format('Y-m-d'),
            'payment_deadline_fmt'     => $q->payment_deadline?->format('d M Y'),
            'processing_started_at'    => $q->processing_started_at?->format('d M Y, h:i A'),
            'delivery_ready'           => (bool) $q->delivery_ready,
            'delivered_at'             => $q->delivered_at?->format('d M Y, h:i A'),
            'sftp_delivery_path'       => $q->getSftpDeliveryAbsolutePath(),
            'sftp_delivery_relative'   => $q->getSftpDeliveryRelativePath(),
            'created_at'               => $q->created_at->format('d M Y, h:i A'),
            'updated_at'               => $q->updated_at->format('d M Y, h:i A'),
        ];
    }

    /**
     * Proactively checks if the delivery directory contains files.
     */
    protected function autoCheckAndMarkDeliveryReady(Inquiry $inquiry): bool
    {
        if ($inquiry->status !== 'completed' || $inquiry->delivery_ready) {
            return (bool) $inquiry->delivery_ready;
        }

        try {
            $relativePath = $inquiry->getSftpDeliveryRelativePath();
            $absolutePath = $inquiry->getSftpDeliveryAbsolutePath();
            $exists = false;

            $disk = Storage::disk('sftp_delivery');
            if ($disk->exists($relativePath)) {
                $contents = $disk->listContents($relativePath)->toArray();
                $exists = count($contents) > 0;
            }

            if ($exists) {
                $updateData = ['delivery_ready' => true];
                if (!$inquiry->delivered_at) {
                    $updateData['delivered_at'] = now();
                }
                $inquiry->update($updateData);
                $inquiry->refresh();

                // Send email notification to user
                try {
                    $recipient = $inquiry->user_email;
                    Mail::to($recipient)->send(new TilesReadyNotification($inquiry));
                    Log::info('TilesReadyNotification sent via autocheck to ' . $recipient . ' for ' . $inquiry->inquiry_id);
                } catch (\Exception $e) {
                    Log::error('Failed to send TilesReadyNotification via autocheck for ' . $inquiry->inquiry_id . ': ' . $e->getMessage());
                }

                return true;
            }
        } catch (\Exception $e) {
            Log::warning('autoCheckAndMarkDeliveryReady failed for ' . $inquiry->inquiry_id . ': ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Client: check the status of a given inquiry.
     */
    public function clientCheckStatus($id)
    {
        $user = Auth::user();
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Perform auto check to detect uploaded files on status request
        $this->autoCheckAndMarkDeliveryReady($inquiry);

        return response()->json([
            'success'        => true,
            'status'         => $inquiry->status,
            'delivery_ready' => (bool) $inquiry->delivery_ready,
            'delivered_at'   => $inquiry->delivered_at ? $inquiry->delivered_at->format('d M Y, h:i A') : null,
        ]);
    }

    /**
     * Rewrite a stored thumbnail URL to always use the current app URL.
     */
    private function rewriteThumbnailUrl(?string $url, ?string $mapDataID = null): string
    {
        if (!empty($url)) {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                $appUrl  = url('/');
                $appHost = parse_url($appUrl, PHP_URL_HOST);
                $urlHost = parse_url($url, PHP_URL_HOST);

                if ($urlHost && $urlHost !== $appHost && !in_array($urlHost, ['localhost', '127.0.0.1'])) {
                    return $url;
                }

                $path = parse_url($url, PHP_URL_PATH);
            } else {
                $path = $url;
            }

            if (!empty($path)) {
                $path        = '/' . ltrim($path, '/');
                $decodedPath = urldecode($path);

                if (file_exists(public_path($decodedPath)) || file_exists(public_path($path))) {
                    return url($path);
                }
            }
        }

        if (!empty($mapDataID)) {
            $filename     = strtolower(preg_replace('/[\s\-]+/', '_', $mapDataID)) . '_pin_image.jpg';
            $fallbackPath = '/assets/img/front-pages/locations/' . $filename;

            if (file_exists(public_path($fallbackPath))) {
                return url($fallbackPath);
            }
        }

        return '';
    }

    /**
     * Download the selected area coordinates of an inquiry as a KML file.
     * Accessible by admins.
     */
    public function adminDownloadKml($id)
    {
        $inquiry = Inquiry::findOrFail($id);
        $coordsPayload = $inquiry->area_coordinates;

        $coordinatesArray = [];
        if (!empty($coordsPayload) && isset($coordsPayload['type']) && $coordsPayload['type'] === 'Polygon') {
            $coordinatesArray = $coordsPayload['coordinates'][0] ?? [];
        } elseif (is_array($coordsPayload)) {
            $coordinatesArray = $coordsPayload;
        }

        if (empty($coordinatesArray)) {
            return redirect()->back()->with('error', 'No coordinates found for this inquiry.');
        }

        // Ensure the polygon closes (last point equals first point)
        $first = reset($coordinatesArray);
        $last = end($coordinatesArray);

        $firstLng = $first[0] ?? $first['lng'] ?? $first['longitude'] ?? null;
        $firstLat = $first[1] ?? $first['lat'] ?? $first['latitude'] ?? null;
        $lastLng = $last[0] ?? $last['lng'] ?? $last['longitude'] ?? null;
        $lastLat = $last[1] ?? $last['lat'] ?? $last['latitude'] ?? null;

        if ($firstLng !== $lastLng || $firstLat !== $lastLat) {
            $coordinatesArray[] = $first;
        }

        // Build the coordinates text string for KML
        $kmlCoordinates = '';
        foreach ($coordinatesArray as $point) {
            $lng = $point[0] ?? $point['lng'] ?? $point['longitude'] ?? null;
            $lat = $point[1] ?? $point['lat'] ?? $point['latitude'] ?? null;
            if ($lng !== null && $lat !== null) {
                $kmlCoordinates .= "{$lng},{$lat},0 ";
            }
        }
        $kmlCoordinates = trim($kmlCoordinates);

        // Build KML XML content
        $kmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $kmlContent .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $kmlContent .= '  <Document>' . "\n";
        $kmlContent .= "    <name>Inquiry_#{$inquiry->inquiry_id}</name>\n";
        
        // Translucent Purple Styling Block (#696cff at 35% opacity)
        // KML Color syntax is AABBGGRR (Alpha, Blue, Green, Red)
        // #696cff -> R=69 (105), G=6c (108), B=ff (255)
        // Alpha: 35% -> 0.35 * 255 = ~89 -> 59 in hex
        // Border: solid -> 100% -> ff in hex
        // Color border: fffff6c69 (solid purpleish)
        // Color polygon: 59ff6c69 (translucent purpleish)
        $kmlContent .= '    <Style id="purpleArea">' . "\n";
        $kmlContent .= '      <LineStyle><color>ffff6c69</color><width>2</width></LineStyle>' . "\n"; 
        $kmlContent .= '      <PolyStyle><color>59ff6c69</color><fill>1</fill><outline>1</outline></PolyStyle>' . "\n";
        $kmlContent .= '    </Style>' . "\n";
        
        // Placemark
        $kmlContent .= '    <Placemark>' . "\n";
        $kmlContent .= "      <name>User Selected Area (Inquiry ID: {$inquiry->inquiry_id})</name>\n";
        $kmlContent .= '      <styleUrl>#purpleArea</styleUrl>' . "\n";
        $kmlContent .= '      <Polygon>' . "\n";
        $kmlContent .= '        <outerBoundaryIs><LinearRing><coordinates>' . "\n";
        $kmlContent .= "          {$kmlCoordinates}\n";
        $kmlContent .= '        </coordinates></LinearRing></outerBoundaryIs>' . "\n";
        $kmlContent .= '      </Polygon>' . "\n";
        $kmlContent .= '    </Placemark>' . "\n";
        $kmlContent .= '  </Document>' . "\n";
        $kmlContent .= '</kml>';

        $fileName = "inquiry_{$inquiry->inquiry_id}_boundary.kml";

        return response($kmlContent)
            ->header('Content-Type', 'application/vnd.google-earth.kml+xml')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"")
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Return a secure tileset URL for previewing the 3D model for a completed inquiry.
     * Strategy mirrors clientDownload: local-first, SFTP fallback with auto-extract.
     */
    public function getInquiryPreviewTilesetConfig($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // 1. Find the inquiry belonging to this user
        $inquiry = Inquiry::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // 2. Security: only completed inquiries with delivery_ready
        if ($inquiry->status !== 'completed' || !$inquiry->delivery_ready) {
            return response()->json([
                'success' => false,
                'message' => 'Inquiry 3D model is not yet available for preview.',
            ], 403);
        }

        // 3. Resolve paths — mirrors getSftpDeliveryRelativePath / getSftpDeliveryAbsolutePath
        $relativeDeliveryDir = $inquiry->getSftpDeliveryRelativePath(); // "inquiry_deliveries/{inquiry_id}"
        $absoluteDeliveryDir = $inquiry->getSftpDeliveryAbsolutePath(); // SYSTEM_SSH_STORAGE_ROOT/inquiry_deliveries/{inquiry_id}
        $absoluteTilesetPath = rtrim($absoluteDeliveryDir, '/') . '/tileset.json';

        // ─── STEP A: Check if tileset.json already exists locally ────────────
        if (file_exists($absoluteTilesetPath)) {
            // Serve from local via viewer_assets (relative to sftp_delivery disk root)
            $relTileset = ltrim(str_replace(
                rtrim(env('SYSTEM_SSH_STORAGE_ROOT', '/home/dataportal/sftpgo/sftpgo_home/data'), '/') . '/',
                '',
                $absoluteTilesetPath
            ), '/');
            return response()->json([
                'success'     => true,
                'inquiry_id'  => $inquiry->inquiry_id,
                'tileset_url' => route('viewer_assets', ['path' => $relTileset]),
            ]);
        }

        // ─── STEP B: Look for a ZIP in the local delivery folder and extract ─
        if (is_dir($absoluteDeliveryDir)) {
            $localZips = array_filter(glob(rtrim($absoluteDeliveryDir, '/') . '/*.zip') ?: [], 'file_exists');
            foreach ($localZips as $zipFile) {
                try {
                    $zip = new \ZipArchive();
                    if ($zip->open($zipFile) === true) {
                        $zip->extractTo($absoluteDeliveryDir);
                        $zip->close();
                        if (file_exists($absoluteTilesetPath)) {
                            $relTileset = ltrim(str_replace(
                                rtrim(env('SYSTEM_SSH_STORAGE_ROOT', '/home/dataportal/sftpgo/sftpgo_home/data'), '/') . '/',
                                '',
                                $absoluteTilesetPath
                            ), '/');
                            return response()->json([
                                'success'     => true,
                                'inquiry_id'  => $inquiry->inquiry_id,
                                'tileset_url' => route('viewer_assets', ['path' => $relTileset]),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Inquiry 3D Preview local ZIP extract failed [{$zipFile}]: " . $e->getMessage());
                }
            }
        }

        // ─── STEP C: Check SFTP disk (plain relativePath only — disk root is '/') ─
        $disk = Storage::disk('sftp_delivery');
        $relativeTilesetPath = null;

        try {
            if ($disk->exists($relativeDeliveryDir)) {
                // First try: look for already-extracted tileset.json
                $sftpFiles = $disk->allFiles($relativeDeliveryDir);
                foreach ($sftpFiles as $sftpFile) {
                    if (basename($sftpFile) === 'tileset.json') {
                        $relativeTilesetPath = $sftpFile;
                        break;
                    }
                }

                // Second try: look for a ZIP and extract it to local
                if (!$relativeTilesetPath) {
                    $sftpZipFile = null;
                    foreach ($disk->files($relativeDeliveryDir) as $f) {
                        if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'zip') {
                            $sftpZipFile = $f;
                            break;
                        }
                    }

                    if ($sftpZipFile) {
                        // Ensure local extraction directory exists
                        if (!is_dir($absoluteDeliveryDir)) {
                            @mkdir($absoluteDeliveryDir, 0777, true);
                        }

                        $tempZip = tempnam(sys_get_temp_dir(), 'inq_preview_');
                        try {
                            // Download ZIP from SFTP and extract locally
                            $zipStream = $disk->readStream($sftpZipFile);
                            if ($zipStream) {
                                file_put_contents($tempZip, $zipStream);
                                fclose($zipStream);
                            }

                            $zip = new \ZipArchive();
                            if ($zip->open($tempZip) === true) {
                                $zip->extractTo($absoluteDeliveryDir);
                                $zip->close();

                                if (file_exists($absoluteTilesetPath)) {
                                    $relTileset = ltrim(str_replace(
                                        rtrim(env('SYSTEM_SSH_STORAGE_ROOT', '/home/dataportal/sftpgo/sftpgo_home/data'), '/') . '/',
                                        '',
                                        $absoluteTilesetPath
                                    ), '/');
                                    return response()->json([
                                        'success'     => true,
                                        'inquiry_id'  => $inquiry->inquiry_id,
                                        'tileset_url' => route('viewer_assets', ['path' => $relTileset]),
                                    ]);
                                }
                            }
                        } finally {
                            @unlink($tempZip);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Inquiry 3D Preview SFTP scan failed for [{$relativeDeliveryDir}]: " . $e->getMessage());
        }

        // ─── STEP D: If tileset found on SFTP (non-zipped), stream via viewer_assets ─
        if ($relativeTilesetPath && $disk->exists($relativeTilesetPath)) {
            return response()->json([
                'success'     => true,
                'inquiry_id'  => $inquiry->inquiry_id,
                'tileset_url' => route('viewer_assets', ['path' => $relativeTilesetPath]),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Inquiry 3D model preview tileset not found on delivery storage.',
            'debug'   => [
                'relative_delivery_dir'  => $relativeDeliveryDir,
                'absolute_delivery_dir'  => $absoluteDeliveryDir,
                'absolute_tileset_path'  => $absoluteTilesetPath,
                'local_dir_exists'       => is_dir($absoluteDeliveryDir),
                'local_tileset_exists'   => file_exists($absoluteTilesetPath),
                'sftp_dir_accessible'    => $disk->exists($relativeDeliveryDir),
            ],
        ], 404);
    }
}
