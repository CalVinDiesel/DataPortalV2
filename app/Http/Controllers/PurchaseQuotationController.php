<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseQuotation;
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

        return view('portal.purchase-quotation-new', compact('purchaseId'));
    }

    /**
     * Store a newly created purchase quotation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|string|unique:purchase_quotations,purchase_id',
            'output_categories' => 'required|array',
            'output_categories.*' => 'string',
            'area_coordinates' => 'nullable|array',
        ]);

        $user = Auth::user();

        $quotation = PurchaseQuotation::create([
            'purchase_id' => $request->purchase_id,
            'user_id' => $user->id,
            'user_email' => $user->email,
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
