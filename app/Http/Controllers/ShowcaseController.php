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

        // Return only the unique ones with their joined map data
        $showcases = Showcase::whereIn('showcases.id', $uniqueIds)
            ->select('showcases.*', 'map_data.title', 'map_data.thumbNailUrl', 'map_data.description')
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

        $showcases = Showcase::create([
            'map_data_id' => $request->map_data_id,
            'display_order' => $request->display_order,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $showcases]);
    }

    public function update(Request $request, $id)
    {
        $showcases = Showcase::find($id);
        if (!$showcases) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        if ($request->has('display_order')) {
            $showcases->display_order = $request->display_order;
            $showcases->save();
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

        if ($request->query('from') === 'both') {
            \App\Models\MapData::where('mapDataID', $map_dataId)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Removed successfully.']);
    }
}
