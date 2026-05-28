<?php

namespace App\Http\Controllers;

use App\Models\Showcase;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    /**
     * Normalize an ID for comparison (lowercase and remove all non-alphanumeric chars).
     */
    private function normalizeId($id)
    {
        if (!$id) return '';
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $id));
    }

    public function index()
    {
        // v176: Fetch all to perform logical cleanup
        $all = Showcase::orderBy('display_order', 'asc')->get();
        
        $seenNormalized = [];
        $uniqueIds = [];
        
        foreach ($all as $s) {
            $norm = $this->normalizeId($s->map_data_id);
            if (isset($seenNormalized[$norm])) {
                // Already have this logical ID — delete the duplicate row!
                $s->delete();
            } else {
                $seenNormalized[$norm] = true;
                $uniqueIds[] = $s->id;
            }
        }

        // Auto-heal display order gaps or duplicates on every page load / hard refresh
        $this->renumber();

        // Return only the unique ones with their joined map data
        $showcases = Showcase::select('showcases.*', 'map_data.title', 'map_data.thumbNailUrl', 'map_data.description')
            ->leftJoin('map_data', 'showcases.map_data_id', '=', 'map_data.mapDataID')
            ->orderBy('showcases.display_order', 'asc')
            ->get();
            
        return response()->json($showcases);
    }

    public function store(Request $request)
    {
        $request->validate([
            'map_data_id' => 'required|string',
            'display_order' => 'required|integer',
        ]);

        $normIncoming = $this->normalizeId($request->map_data_id);
        
        // v176: Robust check against normalized existing IDs
        $all = Showcase::all();
        $exists = $all->first(function($s) use ($normIncoming) {
            return $this->normalizeId($s->map_data_id) === $normIncoming;
        });

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This specific 3D model showcase has been added into the showcase already.']);
        }

        $targetOrder = intval($request->display_order);

        // Shift all showcases with display_order >= targetOrder by 1
        Showcase::where('display_order', '>=', $targetOrder)
            ->increment('display_order');

        $showcases = Showcase::create([
            'map_data_id' => $request->map_data_id,
            'display_order' => $targetOrder,
            'created_at' => now(),
        ]);

        // Renumber all sequentially
        $this->renumber();

        // Refresh the model to get the updated renumbered order
        $showcases->refresh();

        return response()->json(['success' => true, 'data' => $showcases]);
    }

    public function update(Request $request, $id)
    {
        $showcases = Showcase::find($id);
        if (!$showcases) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        if ($request->has('display_order')) {
            $targetOrder = intval($request->display_order);

            // Shift all showcases with display_order >= targetOrder (excluding current showcase) by 1
            Showcase::where('display_order', '>=', $targetOrder)
                ->where('id', '!=', $showcases->id)
                ->increment('display_order');

            $showcases->display_order = $targetOrder;
            $showcases->save();

            // Renumber all sequentially
            $this->renumber();
        }

        return response()->json(['success' => true, 'data' => $showcases]);
    }

    public function destroy(Request $request, $id)
    {
        $showcases = Showcase::find($id);
        if (!$showcases) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $map_dataId = $showcases->map_data_id;
        $showcases->delete();

        // Renumber remaining ones to fill the gap
        $this->renumber();

        if ($request->query('from') === 'both') {
            \App\Models\MapData::where('mapDataID', $map_dataId)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Removed successfully.']);
    }

    /**
     * Sequentially renumbers all showcases to heal gaps and duplicates.
     */
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
