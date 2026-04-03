<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuildingCollection;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function getBuildings()
    {
        return BuildingCollection::make(
            Building::query()->get()
        )->selectable(false);
    }

    public function addBuilding(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $building = Building::create([
            'name' => $data['name'],
        ]);

        return response()->json([
            'success' => true,
            'building' => $building,
        ], 201);
    }
}