<?php

namespace App\Http\Controllers;

use App\Models\Showcase;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function index()
    {
        $showcase = Showcase::select('Showcase.*', 'MapData.title', 'MapData.thumbNailUrl', 'MapData.description')
            ->leftJoin('MapData', 'Showcase.map_data_id', '=', 'MapData.mapDataID')
            ->orderBy('Showcase.display_order', 'asc')
            ->get();
            
        return response()->json($showcase);
    }

    public function store(Request $request)
    {
        $request->validate([
            'map_data_id' => 'required|string',
            'display_order' => 'required|integer',
        ]);

        $exists = Showcase::where('map_data_id', $request->map_data_id)->first();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This specific 3D model showcase has been added into the showcase already.']);
        }

        $showcase = Showcase::create([
            'map_data_id' => $request->map_data_id,
            'display_order' => $request->display_order,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $showcase]);
    }

    public function update(Request $request, $id)
    {
        $showcase = Showcase::find($id);
        if (!$showcase) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        if ($request->has('display_order')) {
            $showcase->display_order = $request->display_order;
            $showcase->save();
        }

        return response()->json(['success' => true, 'data' => $showcase]);
    }

    public function destroy(Request $request, $id)
    {
        $showcase = Showcase::find($id);
        if (!$showcase) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $mapDataId = $showcase->map_data_id;
        $showcase->delete();

        if ($request->query('from') === 'both') {
            \App\Models\MapData::where('mapDataID', $mapDataId)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Removed successfully.']);
    }
}
