<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseQuotation;
use App\Models\MapData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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

        // Rewrite thumbnail URLs to always point to current server
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
            'purchase_id' => 'required|string|unique:purchase_quotations,purchase_id',
            'map_data_id' => 'required|string|exists:map_data,mapDataID',
            'output_categories' => 'required|array',
            'output_categories.*' => 'string',
            'area_coordinates' => 'required|array',
        ]);

        $user = Auth::user();

        $quotation = PurchaseQuotation::create([
            'purchase_id' => $request->purchase_id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'map_data_id' => $request->map_data_id,
            'output_categories' => $request->output_categories,
            'area_coordinates' => $request->area_coordinates,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase quotation request sent successfully.',
            'data' => $quotation
        ]);
    }

    /**
     * Rewrite a stored thumbnail URL to always use the current app URL.
     */
    private function rewriteThumbnailUrl(?string $url, ?string $mapDataID = null): string
    {
        if (!empty($url)) {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                $appUrl = url('/');
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
                $path = '/' . ltrim($path, '/');
                $decodedPath = urldecode($path);
                
                if (file_exists(public_path($decodedPath)) || file_exists(public_path($path))) {
                    return url($path);
                }
            }
        }

        if (!empty($mapDataID)) {
            $filename = strtolower(preg_replace('/[\s\-]+/', '_', $mapDataID)) . '_pin_image.jpg';
            $fallbackPath = '/assets/img/front-pages/locations/' . $filename;
            
            if (file_exists(public_path($fallbackPath))) {
                return url($fallbackPath);
            }
        }

        return '';
    }

    /**
     * Display a listing of the user's purchase quotations.
     */
    public function my()
    {
        $quotations = PurchaseQuotation::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.purchase-quotation-my', compact('quotations'));
    }
}
