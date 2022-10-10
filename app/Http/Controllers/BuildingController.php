<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
   public function getBuildings() {
       $response = Building::all();
       return response()->json($response);
   }
}
