<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookshelfCollection;
use App\Http\Resources\RoomCollection;
use App\Http\Resources\RoomResource;
use App\Http\Resources\ShelfCollection;
use App\Models\Bookshelf;
use App\Models\Room;
use App\Models\Shelf;
use Illuminate\Http\Request;

class RoomController extends Controller
{
   public function getRooms(Request $request){
       $idBuilding = $request->idBuilding;
       $response = Room::getRooms($idBuilding);
       return response()->json($response);
   }
   public function getAll(){
       return new ShelfCollection(Shelf::all());
   }
}
