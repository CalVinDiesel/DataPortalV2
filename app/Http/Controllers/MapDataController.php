<?php

namespace App\Http\Controllers;

use App\Models\MapData;
use Illuminate\Http\Request;

class MapDataController extends Controller
{
    public function index()
    {
        return response()->json(MapData::orderBy('updateDateTime', 'desc')->get());
    }

    public function show($id)
    {
        $data = MapData::where('mapDataID', $id)->first();
        if (!$data) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json($data);
    }
}

