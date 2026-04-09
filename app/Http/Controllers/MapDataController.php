<?php

namespace App\Http\Controllers;

use App\Models\MapData;
use Illuminate\Http\Request;

class MapDataController extends Controller
{
    public function index()
    {
        $items = MapData::orderBy('updateDateTime', 'desc')->get();

        // Rewrite any stored thumbnail URLs to always point to current server
        $items->transform(function ($item) {
            $item->thumbNailUrl = $this->rewriteThumbnailUrl($item->thumbNailUrl);
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
        $data->thumbNailUrl = $this->rewriteThumbnailUrl($data->thumbNailUrl);

        return response()->json($data);
    }

    /**
     * Rewrite a stored thumbnail URL to always use the current app URL.
     * Handles: absolute URLs from old servers (localhost:3000),
     *          relative paths (/uploads/...), and empty values.
     */
    private function rewriteThumbnailUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        // Already a relative path — make it absolute using current app URL
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return url($url);
        }

        // Absolute URL from old server — extract only the path and rewrite to current server
        $path = parse_url($url, PHP_URL_PATH);

        return $path ? url($path) : '';
    }
}