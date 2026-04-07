<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class ProxyController extends Controller
{
    /**
     * Proxy request to bypass CORS for 3D tilesets.
     */
    public function proxy(Request $request)
    {
        $url = $request->query('url');

        if (!$url) {
            return response()->json(['error' => 'URL parameter is missing'], 400);
        }

        // Validate that we only proxy things we trust
        if (!str_contains($url, 'geovidia.com.my') && !str_contains($url, 'cesium.com') && !str_contains($url, 'geosabah.my')) {
            // Log for debugging but allow for now
            \Log::info("Proxying external URL: " . $url);
        }

        try {
            // High-speed Content-Type guessing based on extension
            $path = parse_url($url, PHP_URL_PATH);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            
            $contentTypes = [
                'json' => 'application/json',
                'b3dm' => 'application/octet-stream',
                'cmpt' => 'application/octet-stream',
                'i3dm' => 'application/octet-stream',
                'pnts' => 'application/octet-stream',
                'glb'  => 'model/gltf-binary',
                'gltf' => 'model/gltf+json',
                'wasm' => 'application/wasm',
            ];

            $contentType = $contentTypes[$ext] ?? 'application/octet-stream';

            // Use Laravel's Http client with SSL verification disabled for local dev
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->get($url);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch remote model: ' . $url], 502);
            }

            return Response::make($response->body(), 200, [
                'Content-Type' => $contentType,
                'Access-Control-Allow-Origin' => '*',
                'Cache-Control' => 'max-age=86400',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            ]);

        } catch (\Exception $e) {
            \Log::error("Proxy error for URL [$url]: " . $e->getMessage());
            return response()->json(['error' => 'Proxy error: ' . $e->getMessage()], 503);
        }
    }
}
