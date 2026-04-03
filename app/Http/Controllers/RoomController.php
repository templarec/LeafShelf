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
    public function getRooms(Request $request)
    {
        return RoomCollection::make(Room::getRooms($request->idBuilding));
    }

    public function getAll()
    {
        return new ShelfCollection(Shelf::all());
    }

    public function addRoom(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'building_id' => 'required|integer|exists:buildings,id',
        ]);

        $room = Room::create([
            'name' => $data['name'],
            'building_id' => $data['building_id'],
        ]);

        return response()->json([
            'success' => true,
            'room' => $room,
        ], 201);
    }
}
