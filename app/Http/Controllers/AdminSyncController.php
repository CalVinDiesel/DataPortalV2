<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapData;
use App\Models\Showcase;
use Illuminate\Support\Facades\File;

class AdminSyncController extends Controller
{
    /**
     * Normalize an ID for comparison (lowercase and remove all non-alphanumeric chars).
     * Prevents duplication like "wisma-merdeka" vs "wismamerdeka".
     */
    private function normalizeId($id)
    {
        if (!$id) return '';
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $id));
    }

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
            $incomingId = $loc['id'] ?? '';
            $normIncoming = $this->normalizeId($incomingId);
            
            // v176: Find ALL existing pins that logically match this ID
            $allMatches = MapData::all()->filter(function($p) use ($normIncoming) {
                return $this->normalizeId($p->mapDataID) === $normIncoming;
            });

            // Identify the "Official" one (prefer exact ID match, or just the first one)
            $official = $allMatches->first(function($p) use ($incomingId) {
                return $p->mapDataID === $incomingId;
            }) ?: $allMatches->first();

            $targetId = $official ? $official->mapDataID : $incomingId;

            // Search and Destroy: Delete all OTHER duplicates found in the DB
            foreach ($allMatches as $match) {
                if ($match->mapDataID !== $targetId) {
                    $match->delete();
                }
            }

            // Update the official record
            $incomingThumb = $loc['previewImage'] ?? $loc['thumbnailUrl'] ?? null;
            $finalThumb = (!is_null($incomingThumb) && trim($incomingThumb) !== '') 
                ? $incomingThumb 
                : ($official ? $official->thumbNailUrl : null);

            MapData::updateOrCreate(
                ['mapDataID' => $targetId],
                [
                    'title' => $loc['name'] ?? null,
                    'description' => $loc['description'] ?? null,
                    'xAxis' => $loc['coordinates']['longitude'] ?? 0,
                    'yAxis' => $loc['coordinates']['latitude'] ?? 0,
                    '3dTiles' => $loc['dataPaths']['tileset'] ?? null,
                    'thumbNailUrl' => $finalThumb,
                    'updateDateTime' => now(),
                ]
            );
            $count++;
        }

        return response()->json(['success' => true, 'message' => "$count pins synced. All duplicates were identified and removed."]);
    }

    public function seedShowcasesFromLocations()
    {
        $path = public_path('data/showcases.json');
        if (!File::exists($path)) {
            return response()->json(['success' => false, 'message' => 'showcases.json not found']);
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (!isset($data['showcases']) || !is_array($data['showcases'])) {
            return response()->json(['success' => false, 'message' => 'Invalid showcases.json format']);
        }

        $allPins = MapData::all();
        $countAdded = 0;
        $countRemoved = 0;
        $countUpdated = 0;

        // 1. Clean up orphaned or duplicate showcase records
        $allShowcases = Showcase::all();
        foreach ($allShowcases as $s) {
            $sId = $s->map_data_id;
            $normId = $this->normalizeId($sId);

            $officialPin = $allPins->first(function($p) use ($normId) {
                return $this->normalizeId($p->mapDataID) === $normId;
            });

            if (!$officialPin) {
                $s->delete();
                $countRemoved++;
                continue;
            }

            $duplicates = Showcase::all()->filter(function($other) use ($normId, $s) {
                return $other->id !== $s->id && $this->normalizeId($other->map_data_id) === $normId;
            });

            foreach ($duplicates as $dup) {
                $dup->delete();
                $countRemoved++;
            }

            if ($s->map_data_id !== $officialPin->mapDataID) {
                $s->map_data_id = $officialPin->mapDataID;
                $s->save();
                $countUpdated++;
            }
        }

        // 2. Add missing showcase items from showcases.json
        $currentShowcases = Showcase::all();
        $order = $currentShowcases->max('display_order');
        $order = ($order !== null) ? intval($order) : -1;

        foreach ($data['showcases'] as $item) {
            $mapDataId = $item['mapDataID'] ?? '';
            $normId = $this->normalizeId($mapDataId);

            $officialPin = $allPins->first(function($p) use ($normId) {
                return $this->normalizeId($p->mapDataID) === $normId;
            });

            if (!$officialPin) {
                continue; // Skip if referenced map pin does not exist in DB
            }

            $hasShowcase = $currentShowcases->contains(function($s) use ($normId) {
                return $this->normalizeId($s->map_data_id) === $normId;
            });

            if (!$hasShowcase) {
                $order++;
                Showcase::create([
                    'map_data_id' => $officialPin->mapDataID,
                    'display_order' => $item['display_order'] ?? $order,
                    'created_at' => now(),
                ]);
                $countAdded++;
            }
        }

        // 3. Renumber all showcase items display orders sequentially
        $this->renumber();

        return response()->json([
            'success' => true, 
            'message' => "Showcase synced. Added $countAdded new showcases, removed $countRemoved orphans/duplicates, and updated $countUpdated IDs."
        ]);
    }

    public function exportShowcasesJson()
    {
        $showcases = Showcase::select('showcases.*', 'map_data.title', 'map_data.thumbNailUrl', 'map_data.description', 'map_data.3dTiles as tileset_path')
            ->leftJoin('map_data', 'showcases.map_data_id', '=', 'map_data.mapDataID')
            ->orderBy('showcases.display_order', 'asc')
            ->get();

        $items = [];
        foreach ($showcases as $row) {
            $items[] = [
                'mapDataID' => $row->map_data_id,
                'title' => $row->title,
                'description' => $row->description,
                'thumbNailUrl' => $row->thumbNailUrl,
                '3dTiles' => $row->tileset_path,
                'display_order' => (int) $row->display_order
            ];
        }

        $path = public_path('data/showcases.json');
        File::put($path, json_encode(['showcases' => $items], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json(['success' => true, 'message' => 'Exported successfully to showcases.json']);
    }

    public function showcasesRenumber()
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
        $map_data = MapData::all();
        $locations = [];

        foreach ($map_data as $row) {
            $tileset = $row->getAttribute('3dTiles');
            
            // Skip records that look like client uploads to keep locations.json clean
            if (str_contains($tileset, 'nitro') || str_contains($tileset, 'delivered')) {
                continue;
            }

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
                    'tileset' => $tileset
                ],
                'thumbnailUrl' => $row->thumbNailUrl
            ];
        }

        $path = public_path('data/locations.json');
        File::put($path, json_encode(['locations' => $locations], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json(['success' => true, 'message' => 'Exported successfully to locations.json']);
    }

    private function renumber()
    {
        $items = Showcase::orderBy('display_order', 'asc')->orderBy('id', 'asc')->get();
        $order = 0;
        foreach ($items as $item) {
            $item->display_order = $order++;
            $item->save();
        }
    }
}
