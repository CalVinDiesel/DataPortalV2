<?php

namespace App\Http\Controllers;

use App\Models\MapData;
use Illuminate\Http\Request;

class MapDataController extends Controller
{
    public function index()
    {
        $items = MapData::orderBy('title', 'asc')->get();

        // Rewrite any stored thumbnail URLs to always point to current server
        $items->transform(function ($item) {
            $item->thumbNailUrl = $this->rewriteThumbnailUrl($item->thumbNailUrl, $item->mapDataID);
            return $item;
        });

        return response()->json($items);
    }

    public function show($id)
    {
        $data = MapData::where('mapDataID', $id)->first();

        if (!$data) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Rewrite thumbnail URL for single record too
        $data->thumbNailUrl = $this->rewriteThumbnailUrl($data->thumbNailUrl, $data->mapDataID);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapDataID' => 'required|string',
            'title' => 'required|string',
            'xAxis' => 'required|numeric',
            'yAxis' => 'required|numeric',
            '3dTiles' => 'required|string',
        ]);

        // 🛡️ SAFETY GUARD: Prevent client-side processed data from entering Map Pins table
        $tilesetUrl = $request->input('3dTiles');
        if (str_contains($tilesetUrl, 'nitro') || str_contains($tilesetUrl, 'delivered')) {
            return response()->json([
                'success' => false,
                'message' => 'This path appears to be a private client upload or delivery. Map Pins are restricted to public site locations only.'
            ], 422);
        }

        $isUpdate = $request->input('is_update', false);

        if (!$isUpdate) {
            // 🛡️ DUPLICATE CHECK (v176): CASE-INSENSITIVE (Postgres requires double quotes for CamelCase columns)
            $existing = MapData::whereRaw('LOWER("mapDataID") = ?', [strtolower($request->mapDataID)])->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => "A 3D model with the ID '{$request->mapDataID}' already exists. Please use a unique Model ID."
                ], 422);
            }
        }

        $data = MapData::updateOrCreate(
            ['mapDataID' => $request->mapDataID],
            [
                'title' => $request->title,
                'description' => $request->description,
                'xAxis' => $request->xAxis,
                'yAxis' => $request->yAxis,
                '3dTiles' => $request->input('3dTiles'),
                'thumbNailUrl' => $request->thumbNailUrl,
                'updateDateTime' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Map pin updated successfully', 'data' => $data]);
    }

    public function destroy($id)
    {
        $data = MapData::where('mapDataID', $id)->first();
        if ($data) {
            $data->delete();
            return response()->json(['success' => true, 'message' => 'Map pin deleted successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Pin not found'], 404);
    }

    /**
     * Rewrite a stored thumbnail URL to always use the current app URL.
     * Checks if the file actually exists to prevent returning 404 URLs to the frontend.
     */
    private function rewriteThumbnailUrl(?string $url, ?string $mapDataID = null): string
    {
        // 1. Check explicitly provided URL
        if (!empty($url)) {
            // v176: Robust external URL detection. 
            // If it's a Cloudinary URL or starts with http and isn't our own domain, trust it.
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                $appUrl = url('/');
                $appHost = parse_url($appUrl, PHP_URL_HOST);
                $urlHost = parse_url($url, PHP_URL_HOST);
                
                // Always trust Cloudinary
                if ($urlHost && str_contains($urlHost, 'cloudinary.com')) {
                    return $url;
                }
                
                // If it's a different host and not a local dev address, trust it
                if ($urlHost && $urlHost !== $appHost && !in_array($urlHost, ['localhost', '127.0.0.1'])) {
                    return $url;
                }
                
                // Otherwise it's an absolute URL pointing to our own server, extract path
                $path = parse_url($url, PHP_URL_PATH);
            } else {
                $path = $url;
            }

            if (!empty($path)) {
                // Ensure path starts with a slash
                $path = '/' . ltrim($path, '/');
                $decodedPath = urldecode($path);
                
                // If it exists exactly as is (or decoded) in public
                if (file_exists(public_path($decodedPath)) || file_exists(public_path($path))) {
                    return url($path);
                }
            }
        }

        // 2. Try the auto-derived fallback path from mapDataID
        if (!empty($mapDataID)) {
            // e.g. "KB_3DTiles_Lite" -> "kb_3dtiles_lite_pin_image.jpg"
            $filename = strtolower(preg_replace('/[\s\-]+/', '_', $mapDataID)) . '_pin_image.jpg';
            $fallbackPath = '/assets/img/front-pages/locations/' . $filename;
            
            if (file_exists(public_path($fallbackPath))) {
                return url($fallbackPath);
            }
        }

        // File genuinely does not exist locally; return empty so frontend uses placeholder
        return '';
    }
}
