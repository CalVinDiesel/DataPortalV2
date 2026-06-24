<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseQuotation;
use App\Models\MapData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\QuotationReceived;
use App\Mail\AdminNewQuotationAlert;
use App\Mail\QuotationSentToUser;

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
            'quoted_price'         => 'nullable|numeric|min:0',
            'bank_name'            => 'nullable|string|max:255',
            'bank_account_number'  => 'nullable|string|max:100',
            'bank_account_name'    => 'nullable|string|max:255',
            'payment_deadline'     => 'nullable|date',
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

        // Only update bank/price fields when provided
        if ($request->has('quoted_price'))        $updateData['quoted_price']         = $request->quoted_price;
        if ($request->has('bank_name'))            $updateData['bank_name']             = $request->bank_name;
        if ($request->has('bank_account_number')) $updateData['bank_account_number']   = $request->bank_account_number;
        if ($request->has('bank_account_name'))   $updateData['bank_account_name']     = $request->bank_account_name;
        if ($request->has('payment_deadline'))    $updateData['payment_deadline']      = $request->payment_deadline;

        // Stamp timestamps on key transitions
        if ($newStatus === 'quoted' && $oldStatus !== 'quoted') {
            $updateData['quoted_at'] = now();
        }
        if ($newStatus === 'processing' && $oldStatus !== 'processing') {
            $updateData['processing_started_at'] = now();
        }

        $quotation->update($updateData);
        $quotation->refresh()->load(['user', 'mapData']);

        // Send email to user when admin sends a formal quotation
        $emailSent = false;
        if ($newStatus === 'quoted' && $oldStatus !== 'quoted') {
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

        return response()->json([
            'success'    => true,
            'message'    => 'Quotation updated successfully.' . ($emailSent ? ' Quotation email sent to client.' : ''),
            'email_sent' => $emailSent,
            'data'       => $this->formatQuotationForApi($quotation),
        ]);
    }

    /**
     * Admin: update a single note specifically bound to a status.
     */
    public function adminUpdateSingleNote(Request $request, $id)
    {
        $quotation = PurchaseQuotation::with(['user', 'mapData'])->findOrFail($id);

        $request->validate([
            'status'      => 'required|in:' . implode(',', PurchaseQuotation::STATUSES),
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $statusKey = $request->status;
        $noteText = $request->admin_notes;

        $notes = $quotation->admin_notes;
        if (!is_array($notes)) {
            $notes = [];
        }

        $notes[$statusKey] = $noteText;

        $quotation->update([
            'admin_notes' => $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully.',
            'data'    => $this->formatQuotationForApi($quotation),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Format a quotation model as an array for API responses.
     */
    private function formatQuotationForApi(PurchaseQuotation $q): array
    {
        return [
            'id'                   => $q->id,
            'purchase_id'          => $q->purchase_id,
            'user_id'              => $q->user_id,
            'user_email'           => $q->user_email,
            'user_name'            => $q->user->name ?? '—',
            'map_data_id'          => $q->map_data_id,
            'map_title'            => $q->mapData->title ?? $q->map_data_id,
            'map_3d_tiles'         => $q->mapData->{'3dTiles'} ?? null,
            'map_x_axis'           => $q->mapData->xAxis ?? null,
            'map_y_axis'           => $q->mapData->yAxis ?? null,
            'output_categories'    => $q->output_categories,
            'area_coordinates'     => $q->area_coordinates,
            'status'               => $q->status,
            'status_label'         => $q->status_label,
            'status_color'         => $q->status_color,
            'admin_notes'          => $q->admin_notes,
            'rejection_reason'     => $q->rejection_reason,
            'quoted_price'         => $q->quoted_price,
            'quoted_at'            => $q->quoted_at?->format('d M Y, h:i A'),
            'bank_name'            => $q->bank_name,
            'bank_account_number'  => $q->bank_account_number,
            'bank_account_name'    => $q->bank_account_name,
            'payment_deadline'     => $q->payment_deadline?->format('Y-m-d'),
            'payment_deadline_fmt' => $q->payment_deadline?->format('d M Y'),
            'processing_started_at'=> $q->processing_started_at?->format('d M Y, h:i A'),
            'created_at'           => $q->created_at->format('d M Y, h:i A'),
            'updated_at'           => $q->updated_at->format('d M Y, h:i A'),
        ];
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
