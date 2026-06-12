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
        // Ensure MapData is updated first
        $this->seedMapDataFromLocations();

        $allPins = MapData::all();
        
        $countRemoved = 0;
        $countUpdated = 0;
        $countAdded = 0;

        // 1. Clean up existing showcases
        $allShowcases = Showcase::all();
        foreach ($allShowcases as $s) {
            $sId = $s->map_data_id;
            $normId = $this->normalizeId($sId);

            // Find if this showcase entry logically matches ANY official Map Pin
            $officialPin = $allPins->first(function($p) use ($normId) {
                return $this->normalizeId($p->mapDataID) === $normId;
            });

            if (!$officialPin) {
                // ORPHAN: Delete it.
                $s->delete();
                $countRemoved++;
                continue;
            }

            // DUPLICATE CHECK: Delete other duplicate showcases for this pin.
            $duplicates = Showcase::all()->filter(function($other) use ($normId, $s) {
                return $other->id !== $s->id && $this->normalizeId($other->map_data_id) === $normId;
            });

            foreach ($duplicates as $dup) {
                $dup->delete();
                $countRemoved++;
            }

            // ID ALIGNMENT: Ensure this showcase uses the EXACT ID from MapData
            if ($s->map_data_id !== $officialPin->mapDataID) {
                $s->map_data_id = $officialPin->mapDataID;
                $s->save();
                $countUpdated++;
            }
        }

        // 2. Add missing showcases for existing MapData pins
        $currentShowcases = Showcase::all();
        $order = $currentShowcases->max('display_order');
        $order = ($order !== null) ? intval($order) : -1;

        foreach ($allPins as $pin) {
            $normPinId = $this->normalizeId($pin->mapDataID);
            $hasShowcase = $currentShowcases->contains(function($s) use ($normPinId) {
                return $this->normalizeId($s->map_data_id) === $normPinId;
            });

            if (!$hasShowcase) {
                $order++;
                Showcase::create([
                    'map_data_id' => $pin->mapDataID,
                    'display_order' => $order,
                    'created_at' => now(),
                ]);
                $countAdded++;
            }
        }

        // 3. Renumber all display orders to be clean
        $this->renumber();

        return response()->json([
            'success' => true, 
            'message' => "Showcase synced. Added $countAdded new showcases, removed $countRemoved orphans/duplicates, and updated $countUpdated IDs."
        ]);
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
