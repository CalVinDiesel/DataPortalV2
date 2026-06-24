<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseQuotation;
use App\Models\MapData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\QuotationReceived;
use App\Mail\AdminNewQuotationAlert;
use App\Mail\QuotationSentToUser;
use App\Mail\TilesReadyNotification;

class PurchaseQuotationController extends Controller
{
    /**
     * Show the form for creating a new purchase quotation.
     */
    public function create()
    {
        do {
            $purchaseId = 'PQ-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (PurchaseQuotation::where('purchase_id', $purchaseId)->exists());

        $mapLocations = MapData::orderBy('title', 'asc')->get();

        $mapLocations->transform(function ($item) {
            $item->thumbNailUrl = $this->rewriteThumbnailUrl($item->thumbNailUrl, $item->mapDataID);
            return $item;
        });

        return view('portal.purchase-quotation-new', compact('purchaseId', 'mapLocations'));
    }

    /**
     * Store a newly created purchase quotation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'       => 'required|string|unique:purchase_quotations,purchase_id',
            'map_data_id'       => 'required|string|exists:map_data,mapDataID',
            'output_categories' => 'required|array',
            'output_categories.*' => 'string',
            'area_coordinates'  => 'required|array',
        ]);

        $user = Auth::user();

        $quotation = PurchaseQuotation::create([
            'purchase_id'       => $request->purchase_id,
            'user_id'           => $user->id,
            'user_email'        => $user->email,
            'map_data_id'       => $request->map_data_id,
            'output_categories' => $request->output_categories,
            'area_coordinates'  => $request->area_coordinates,
            'status'            => 'pending',
        ]);

        // Load relations for mail
        $quotation->load(['user', 'mapData']);

        // Send confirmation to user
        try {
            Mail::to($user->email)->send(new QuotationReceived($quotation));
        } catch (\Exception $e) {
            Log::error('QuotationReceived mail failed', [
                'purchase_id' => $quotation->purchase_id,
                'error'       => $e->getMessage(),
            ]);
        }

        // Alert admin
        try {
            $adminEmail = env('SUPER_ADMIN_EMAIL', 'mosestiquan23@gmail.com');
            Mail::to($adminEmail)->send(new AdminNewQuotationAlert($quotation));
        } catch (\Exception $e) {
            Log::error('AdminNewQuotationAlert mail failed', [
                'purchase_id' => $quotation->purchase_id,
                'error'       => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase quotation request sent successfully.',
            'data'    => $quotation,
        ]);
    }

    /**
     * Display a listing of the user's own purchase quotations.
     */
    public function my()
    {
        $quotations = PurchaseQuotation::with(['mapData'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Auto-check and heal delivery_ready status for any completed quotations
        foreach ($quotations as $quotation) {
            if ($quotation->status === 'completed' && !$quotation->delivery_ready) {
                $this->autoCheckAndMarkDeliveryReady($quotation);
            }
        }

        return view('portal.purchase-quotation-my', compact('quotations'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Admin: show the manage purchase quotations page.
     */
    public function adminIndex()
    {
        return view('admin.manage-purchase-quotations');
    }

    /**
     * Admin: return all quotations as JSON (optionally filtered by status).
     */
    public function adminList(Request $request)
    {
        $query = PurchaseQuotation::with(['user', 'mapData'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_id', 'ilike', "%{$search}%")
                  ->orWhere('user_email', 'ilike', "%{$search}%");
            });
        }

        $quotations = $query->get()->map(function ($q) {
            return $this->formatQuotationForApi($q);
        });

        return response()->json($quotations);
    }

    /**
     * Admin: return a single quotation as JSON.
     */
    public function adminShow($id)
    {
        $quotation = PurchaseQuotation::with(['user', 'mapData'])->findOrFail($id);
        return response()->json($this->formatQuotationForApi($quotation));
    }

    /**
     * Admin: update quotation status and optional fields.
     */
    public function adminUpdateStatus(Request $request, $id)
    {
        $quotation = PurchaseQuotation::with(['user', 'mapData'])->findOrFail($id);

        $request->validate([
            'status'               => 'required|in:' . implode(',', PurchaseQuotation::STATUSES),
            'admin_notes'          => 'nullable|string|max:2000',
            'rejection_reason'     => 'nullable|string|max:2000',
            'quotation_pdf'        => 'nullable|file|mimes:pdf|max:20480', // max 20MB
        ]);

        $newStatus = $request->status;
        $oldStatus = $quotation->status;

        $notes = $quotation->admin_notes;
        if (!is_array($notes)) {
            $notes = [];
        }

        if ($request->has('admin_notes')) {
            $notes[$newStatus] = $request->admin_notes;
        }

        $updateData = [
            'status'               => $newStatus,
            'admin_notes'          => $notes,
            'rejection_reason'     => $request->rejection_reason,
        ];

        // Handle quotation PDF upload (only relevant when status = quoted)
        if ($request->hasFile('quotation_pdf') && $request->file('quotation_pdf')->isValid()) {
            // Delete old PDF if it exists
            if ($quotation->quotation_pdf_path && Storage::disk('local')->exists($quotation->quotation_pdf_path)) {
                Storage::disk('local')->delete($quotation->quotation_pdf_path);
            }
            $file = $request->file('quotation_pdf');
            $filename = 'quotation_' . $quotation->purchase_id . '_' . time() . '.pdf';
            $path = $file->storeAs('quotation_pdfs/' . $quotation->purchase_id, $filename, 'local');
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
                $absolutePath = $quotation->getSftpDeliveryAbsolutePath();
                if (!is_dir($absolutePath)) {
                    if (!@mkdir($absolutePath, 0777, true)) {
                        $disk = Storage::disk('sftp_delivery');
                        $dir = $quotation->getSftpDeliveryRelativePath();
                        if (!$disk->exists($dir)) {
                            $disk->makeDirectory($dir);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not pre-create directory for processing PQ [{$quotation->purchase_id}]: " . $e->getMessage());
            }
        }
        // When transitioning INTO completed, stamp the delivered_at timestamp
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $updateData['delivered_at'] = now();

            // Ensure directory exists (local-first, fallback to SFTP)
            try {
                $absolutePath = $quotation->getSftpDeliveryAbsolutePath();
                if (!is_dir($absolutePath)) {
                    if (!@mkdir($absolutePath, 0777, true)) {
                        $disk = Storage::disk('sftp_delivery');
                        $dir = $quotation->getSftpDeliveryRelativePath();
                        if (!$disk->exists($dir)) {
                            $disk->makeDirectory($dir);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not pre-create directory for completed PQ [{$quotation->purchase_id}]: " . $e->getMessage());
            }
        }

        $quotation->update($updateData);
        $quotation->refresh()->load(['user', 'mapData']);

        // Auto-check if delivery files exist immediately when status is updated to completed
        if ($quotation->status === 'completed' && !$quotation->delivery_ready) {
            $this->autoCheckAndMarkDeliveryReady($quotation);
        }

        // Send email to user when admin sends a formal quotation
        // Can be triggered either on first transition OR if admin explicitly requests email send
        $emailSent = false;
        $sendEmail = $request->boolean('send_email', false);

        if ($newStatus === 'quoted' && ($sendEmail || $oldStatus !== 'quoted')) {
            // Only auto-send on first transition; on re-send, require explicit send_email=true
            if ($oldStatus !== 'quoted' || $sendEmail) {
                try {
                    Mail::to($quotation->user_email)->send(new QuotationSentToUser($quotation));
                    $emailSent = true;
                } catch (\Exception $e) {
                    Log::error('QuotationSentToUser mail failed', [
                        'purchase_id' => $quotation->purchase_id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Quotation updated successfully.' . ($emailSent ? ' Quotation email sent to client.' : ''),
            'email_sent' => $emailSent,
            'data'       => $this->formatQuotationForApi($quotation),
        ]);
    }

    /**
     * Admin: stream/download the uploaded quotation PDF.
     */
    public function adminStreamQuotationPdf($id)
    {
        $quotation = PurchaseQuotation::findOrFail($id);

        if (!$quotation->quotation_pdf_path || !Storage::disk('local')->exists($quotation->quotation_pdf_path)) {
            abort(404, 'Quotation PDF not found.');
        }

        $filename = 'Quotation_' . $quotation->purchase_id . '.pdf';
        return Storage::disk('local')->download($quotation->quotation_pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Client: download the formal quotation PDF for their own quotation.
     */
    public function clientDownloadQuotationPdf(Request $request, $id)
    {
        $user = Auth::user();
        $quotation = PurchaseQuotation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$quotation->quotation_pdf_path || !Storage::disk('local')->exists($quotation->quotation_pdf_path)) {
            abort(404, 'Quotation PDF not available yet.');
        }

        $filename = 'Quotation_' . $quotation->purchase_id . '.pdf';
        return Storage::disk('local')->download($quotation->quotation_pdf_path, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }



    // ─────────────────────────────────────────────────────────────────────────
    // DELIVERY MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Admin: toggle the delivery_ready flag on a completed quotation.
     * Before marking ready, verifies at least one file exists in the SFTP delivery folder.
     */
    public function adminToggleDelivery(Request $request, $id)
    {
        $quotation = PurchaseQuotation::with(['user', 'mapData'])->findOrFail($id);

        $request->validate([
            'delivery_ready' => 'required|boolean',
        ]);

        $markReady = (bool) $request->delivery_ready;

        // If trying to mark as ready, verify files actually exist (local-first check, fallback to SFTP)
        if ($markReady) {
            try {
                $relativePath = $quotation->getSftpDeliveryRelativePath();
                $absolutePath = $quotation->getSftpDeliveryAbsolutePath();
                $exists = false;

                if (is_dir($absolutePath)) {
                    $localFiles = array_diff(scandir($absolutePath), ['.', '..']);
                    $exists = count($localFiles) > 0;
                } else {
                    // Try to create local directory
                    @mkdir($absolutePath, 0777, true);
                    
                    // Fallback to SFTP
                    $disk = Storage::disk('sftp_delivery');
                    if (!$disk->exists($relativePath)) {
                        $disk->makeDirectory($relativePath);
                    }
                    try {
                        $contents = $disk->listContents($relativePath)->toArray();
                        $exists = count($contents) > 0;
                    } catch (\Exception $e) {
                        $exists = false;
                    }
                }

                if (!$exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No files found in the delivery folder. Please upload the 3D model tiles via WinSCP first. Path: ' . $quotation->getSftpDeliveryAbsolutePath(),
                    ], 422);
                }
            } catch (\Exception $e) {
                Log::warning('adminToggleDelivery: Could not verify files for ' . $quotation->purchase_id . ': ' . $e->getMessage());
                // Allow marking ready even if check fails (network/permission issue, etc.) — admin is responsible
            }
        }

        $updateData = ['delivery_ready' => $markReady];
        if ($markReady && !$quotation->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        $quotation->update($updateData);
        $quotation->refresh()->load(['user', 'mapData']);

        // Send notification email to client when delivery is marked as ready
        if ($markReady) {
            try {
                $recipient = $quotation->user_email;
                Mail::to($recipient)->send(new TilesReadyNotification($quotation));
                Log::info('TilesReadyNotification sent to ' . $recipient . ' for ' . $quotation->purchase_id);
            } catch (\Exception $e) {
                // Never let a mail failure block the status update
                Log::error('Failed to send TilesReadyNotification for ' . $quotation->purchase_id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => $markReady ? 'Delivery marked as ready. Client has been notified by email.' : 'Delivery marked as not ready. Client download disabled.',
            'data'     => $this->formatQuotationForApi($quotation),
        ]);
    }

    /**
     * Admin: check what files exist on SFTP for a given quotation's delivery folder.
     * Returns file list, total count, and total size.
     */
    public function adminCheckDelivery($id)
    {
        $quotation = PurchaseQuotation::findOrFail($id);

        $relativePath  = $quotation->getSftpDeliveryRelativePath();
        $absolutePath  = $quotation->getSftpDeliveryAbsolutePath();

        try {
            $files = [];
            $totalSize = 0;

            // Local-first check
            if (is_dir($absolutePath)) {
                $localFiles = array_diff(scandir($absolutePath), ['.', '..']);
                foreach ($localFiles as $file) {
                    $filePath = $absolutePath . '/' . $file;
                    if (is_file($filePath)) {
                        $size = filesize($filePath);
                        $files[] = [
                            'name' => $file,
                            'path' => $relativePath . '/' . $file,
                            'size' => $size,
                            'size_human' => $this->formatBytes($size),
                        ];
                        $totalSize += $size;
                    }
                }
            } else {
                // Try creating local directory first
                @mkdir($absolutePath, 0777, true);

                // Fallback to SFTP
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
            }

            return response()->json([
                'success'        => true,
                'sftp_path'      => $absolutePath,
                'file_count'     => count($files),
                'total_size'     => $totalSize,
                'total_size_human' => $this->formatBytes($totalSize),
                'files'          => $files,
            ]);

        } catch (\Exception $e) {
            Log::error('adminCheckDelivery failed for ' . $quotation->purchase_id . ': ' . $e->getMessage());
            return response()->json([
                'success'   => false,
                'sftp_path' => $absolutePath,
                'message'   => 'Could not read delivery folder: ' . $e->getMessage(),
                'files'     => [],
                'file_count'=> 0,
            ]);
        }
    }

    /**
     * Admin: cleanly delete a purchase quotation.
     * Deletes files from the SFTP/local directory, and deletes the database record.
     */
    public function adminDestroy($id)
    {
        $quotation = PurchaseQuotation::findOrFail($id);

        $relativePath = $quotation->getSftpDeliveryRelativePath();
        $absolutePath = $quotation->getSftpDeliveryAbsolutePath();

        // 1. Delete local delivery files and folder (local-first)
        try {
            if (is_dir($absolutePath)) {
                $this->deleteLocalDirRecursive($absolutePath);
                Log::info("adminDestroy: Deleted local folder: " . $absolutePath);
            }
        } catch (\Exception $e) {
            Log::warning("adminDestroy: Failed to delete local folder: " . $e->getMessage());
        }

        // 2. Delete SFTP files and folder (fallback / sync)
        try {
            $disk = Storage::disk('sftp_delivery');
            if ($disk->exists($relativePath)) {
                $disk->deleteDirectory($relativePath);
                Log::info("adminDestroy: Deleted SFTP folder: " . $relativePath);
            }
        } catch (\Exception $e) {
            Log::warning("adminDestroy: Failed to delete SFTP folder: " . $e->getMessage());
        }

        // 3. Delete database record
        $quotation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase quotation and all associated files deleted successfully.',
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
     * Client: download the 3D model tiles for their completed quotation.
     * Streams the delivery folder as a zip (or a single file directly) from SFTP.
     */
    public function clientDownload($id)
    {
        $user      = Auth::user();
        $quotation = PurchaseQuotation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($quotation->status !== 'completed') {
            return response()->json(['error' => 'Your 3D model is not yet ready for download.'], 403);
        }

        if (!$quotation->delivery_ready) {
            return response()->json(['error' => 'Your 3D model tiles are still being prepared. Please check back soon.'], 403);
        }

        // Release session lock early for large file streaming
        session_write_close();
        ignore_user_abort(true);
        if (function_exists('ini_set')) { ini_set('memory_limit', '-1'); }
        set_time_limit(0);

        $relativePath = $quotation->getSftpDeliveryRelativePath();
        $absolutePath = $quotation->getSftpDeliveryAbsolutePath();
        $disk         = Storage::disk('sftp_delivery');
        $zipName      = $quotation->purchase_id . '_3d_tiles.zip';
        $safeZipName  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $zipName);

        // LOCAL-FIRST: if the SFTP server is mounted locally, stream directly
        if (file_exists($absolutePath) && is_readable($absolutePath)) {
            return $this->streamLocalDelivery($absolutePath, $safeZipName);
        }

        // SFTP fallback: list, download, zip on-the-fly
        try {
            $contents = $disk->listContents($relativePath, true)->toArray();

            if (empty($contents)) {
                return response()->json(['error' => 'No delivery files found. Please contact support.'], 404);
            }

            // If exactly one file and it is a zip — stream it directly without re-zipping
            $fileItems = array_filter($contents, fn($c) => $c->isFile());
            if (count($fileItems) === 1) {
                $singleItem = reset($fileItems);
                $singlePath = $singleItem->path();
                $origName   = basename($singlePath);
                $safeName   = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
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
                    'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
                    'Content-Length'      => $size,
                    'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                    'Pragma'              => 'no-cache',
                    'Expires'             => '0',
                    'X-Accel-Buffering'   => 'no',
                ]);
            }

            // Multiple files — download all from SFTP, zip, stream
            $tempDir = storage_path('app/temp_downloads/' . uniqid('pq_dl_'));
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
                // Clean up temp dir regardless
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
            Log::error('clientDownload failed for quotation ' . $quotation->purchase_id . ': ' . $e->getMessage());
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
            $size      = filesize($absolutePath);
            $mime      = Str::endsWith(strtolower($origName), '.zip') ? 'application/zip' : 'application/octet-stream';

            return response()->stream(function () use ($absolutePath) {
                while (ob_get_level() > 0) ob_end_clean();
                $f = fopen($absolutePath, 'rb');
                if ($f) { fpassthru($f); fclose($f); }
            }, 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
                'Content-Length'      => $size,
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                'X-Accel-Buffering'   => 'no',
            ]);
        }

        // Directory — zip on-the-fly
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
     * Format a quotation model as an array for API responses.
     */
    private function formatQuotationForApi(PurchaseQuotation $q): array
    {
        return [
            'id'                    => $q->id,
            'purchase_id'           => $q->purchase_id,
            'user_id'               => $q->user_id,
            'user_email'            => $q->user_email,
            'user_name'             => $q->user->name ?? '—',
            'map_data_id'           => $q->map_data_id,
            'map_title'             => $q->mapData->title ?? $q->map_data_id,
            'map_3d_tiles'          => $q->mapData->{'3dTiles'} ?? null,
            'map_x_axis'            => $q->mapData->xAxis ?? null,
            'map_y_axis'            => $q->mapData->yAxis ?? null,
            'output_categories'     => $q->output_categories,
            'area_coordinates'      => $q->area_coordinates,
            'status'                => $q->status,
            'status_label'          => $q->status_label,
            'status_color'          => $q->status_color,
            'admin_notes'           => $q->admin_notes,
            'rejection_reason'      => $q->rejection_reason,
            'quoted_price'          => $q->quoted_price,
            'quoted_at'             => $q->quoted_at?->format('d M Y, h:i A'),
            'quotation_pdf_path'    => $q->quotation_pdf_path,
            'quotation_pdf_url'     => $q->quotation_pdf_path
                ? url('/api/admin/purchase-quotations/' . $q->id . '/quotation-pdf')
                : null,
            'quotation_pdf_client_url' => $q->quotation_pdf_path
                ? url('/api/purchase-quotation/' . $q->id . '/quotation-pdf')
                : null,
            'bank_name'             => $q->bank_name,
            'bank_account_number'   => $q->bank_account_number,
            'bank_account_name'     => $q->bank_account_name,
            'payment_deadline'      => $q->payment_deadline?->format('Y-m-d'),
            'payment_deadline_fmt'  => $q->payment_deadline?->format('d M Y'),
            'processing_started_at' => $q->processing_started_at?->format('d M Y, h:i A'),
            // Delivery fields
            'delivery_ready'        => (bool) $q->delivery_ready,
            'delivered_at'          => $q->delivered_at?->format('d M Y, h:i A'),
            'sftp_delivery_path'    => $q->getSftpDeliveryAbsolutePath(),
            'sftp_delivery_relative'=> $q->getSftpDeliveryRelativePath(),
            // Timestamps
            'created_at'            => $q->created_at->format('d M Y, h:i A'),
            'updated_at'            => $q->updated_at->format('d M Y, h:i A'),
        ];
    }

    /**
     * Proactively checks if the delivery directory contains files.
     * If files exist and status is completed, automatically updates delivery_ready to true.
     */
    protected function autoCheckAndMarkDeliveryReady(PurchaseQuotation $quotation): bool
    {
        if ($quotation->status !== 'completed' || $quotation->delivery_ready) {
            return (bool) $quotation->delivery_ready;
        }

        try {
            $relativePath = $quotation->getSftpDeliveryRelativePath();
            $absolutePath = $quotation->getSftpDeliveryAbsolutePath();
            $exists = false;

            if (is_dir($absolutePath)) {
                $localFiles = array_diff(scandir($absolutePath), ['.', '..']);
                $exists = count($localFiles) > 0;
            } else {
                // Try fallback to SFTP
                $disk = Storage::disk('sftp_delivery');
                if ($disk->exists($relativePath)) {
                    $contents = $disk->listContents($relativePath)->toArray();
                    $exists = count($contents) > 0;
                }
            }

            if ($exists) {
                $updateData = ['delivery_ready' => true];
                if (!$quotation->delivered_at) {
                    $updateData['delivered_at'] = now();
                }
                $quotation->update($updateData);
                $quotation->refresh();

                // Send email notification to user
                try {
                    $recipient = $quotation->user_email;
                    Mail::to($recipient)->send(new TilesReadyNotification($quotation));
                    Log::info('TilesReadyNotification sent via autocheck to ' . $recipient . ' for ' . $quotation->purchase_id);
                } catch (\Exception $e) {
                    Log::error('Failed to send TilesReadyNotification via autocheck for ' . $quotation->purchase_id . ': ' . $e->getMessage());
                }

                return true;
            }
        } catch (\Exception $e) {
            Log::warning('autoCheckAndMarkDeliveryReady failed for ' . $quotation->purchase_id . ': ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Client: check the status of a given quotation.
     */
    public function clientCheckStatus($id)
    {
        $user = Auth::user();
        $quotation = PurchaseQuotation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Perform auto check to detect uploaded files on status request
        $this->autoCheckAndMarkDeliveryReady($quotation);

        return response()->json([
            'success'        => true,
            'status'         => $quotation->status,
            'delivery_ready' => (bool) $quotation->delivery_ready,
            'delivered_at'   => $quotation->delivered_at ? $quotation->delivered_at->format('d M Y, h:i A') : null,
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
}
