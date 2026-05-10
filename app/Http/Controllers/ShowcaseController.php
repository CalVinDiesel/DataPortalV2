<?php

namespace App\Http\Controllers;

use App\Models\Showcase;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function index()
    {
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

        $exists = Showcase::where('map_data_id', $request->map_data_id)->first();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This specific 3D model showcases has been added into the showcases already.']);
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
