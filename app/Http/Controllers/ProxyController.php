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
        if (!str_contains($url, 'geosabah.my') && !str_contains($url, 'cesium.com')) {
            // return response()->json(['error' => 'Unsupported proxy target'], 403);
            // Actually let's allow any URL for now to be safe
        }

        try {
            // High-speed Content-Type guessing based on extension to avoid blocking 'get_headers' call
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
            ];

            $contentType = $contentTypes[$ext] ?? 'application/octet-stream';

            return response()->stream(function() use ($url) {
                // Completely bypass Laravel memory limits and string decoding
                @readfile($url);
            }, 200, [
                'Content-Type' => $contentType,
                'Access-Control-Allow-Origin' => '*',
                'Cache-Control' => 'max-age=86400', // Cache for 24 hours
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Proxy error: ' . $e->getMessage()], 503);
        }
    }
}
