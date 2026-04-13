<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapData;
use App\Models\Showcase;
use Illuminate\Support\Facades\File;

class AdminSyncController extends Controller
{
    public function seedMapDataFromLocations()
    {
        $path = public_path('data/locations.json');
        if (!File::exists($path)) {
            return response()->json(['success' => false, 'message' => 'locations.json not found']);
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (!isset($data['locations']) || !is_array($data['locations'])) {
            return response()->json(['success' => false, 'message' => 'Invalid locations.json format']);
        }

        $count = 0;
        foreach ($data['locations'] as $loc) {
            MapData::updateOrCreate(
                ['mapDataID' => $loc['id']],
                [
                    'title' => $loc['name'] ?? null,
                    'description' => $loc['description'] ?? null,
                    'xAxis' => $loc['coordinates']['longitude'] ?? 0,
                    'yAxis' => $loc['coordinates']['latitude'] ?? 0,
                    '3dTiles' => $loc['dataPaths']['tileset'] ?? null,
                    'updateDateTime' => now(),
                ]
            );
            $count++;
        }

        return response()->json(['success' => true, 'message' => "$count pins synced from locations.json."]);
    }

    public function seedShowcaseFromLocations()
    {
        $path = public_path('data/locations.json');
        if (!File::exists($path)) {
            return response()->json(['success' => false, 'message' => 'locations.json not found']);
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (!isset($data['locations']) || !is_array($data['locations'])) {
            return response()->json(['success' => false, 'message' => 'Invalid locations.json format']);
        }

        $maxOrder = Showcase::max('display_order') ?? -1;

        $count = 0;
        foreach ($data['locations'] as $loc) {
            $exists = Showcase::where('map_data_id', $loc['id'])->exists();
            if (!$exists) {
                $maxOrder++;
                Showcase::create([
                    'map_data_id' => $loc['id'],
                    'display_order' => $maxOrder,
                    'created_at' => now(),
                ]);
                $count++;
            }
        }

        return response()->json(['success' => true, 'message' => "$count new items added to showcase from locations.json."]);
    }

    public function showcaseRenumber()
    {
        $items = Showcase::orderBy('display_order', 'asc')->orderBy('id', 'asc')->get();
        $order = 0;
        foreach ($items as $item) {
            $item->display_order = $order++;
            $item->save();
        }

        return response()->json(['success' => true, 'message' => 'Orders renumbered sequentially.']);
    }

    public function exportLocationsJson()
    {
        $mapData = MapData::all();
        $locations = [];

        foreach ($mapData as $row) {
            $locations[] = [
                'id' => $row->mapDataID,
                'name' => $row->title,
                'description' => $row->description,
                'coordinates' => [
                    'longitude' => (float) $row->xAxis,
                    'latitude' => (float) $row->yAxis,
                    'height' => 50
                ],
                'dataPaths' => [
                    'tileset' => $row->getAttribute('3dTiles')
                ]
            ];
        }

        $path = public_path('data/locations.json');
        File::put($path, json_encode(['locations' => $locations], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json(['success' => true, 'message' => 'Exported successfully to locations.json']);
    }
}
