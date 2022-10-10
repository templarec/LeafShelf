<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
   public function getShelves (Request $request){
        $response = Shelf::getShelves($request->idBookshelf);
        return response()->json($response);
   }
}
