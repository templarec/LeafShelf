<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookshelfCollection;
use App\Models\Book;
use App\Models\Bookshelf;
use Illuminate\Http\Request;

class BookshelfController extends Controller
{
    public function getBookshelves(Request $request)
    {
        return BookshelfCollection::make(Bookshelf::getBookshelves($request->idRoom));
    }

    public function addBS(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'room_id' => 'required|integer|exists:rooms,id',
        ]);

        $bookshelf = Bookshelf::create([
            'name' => $data['name'],
            'room_id' => $data['room_id'],
        ]);

        return response()->json([
            'success' => true,
            'bookshelf' => $bookshelf,
        ], 201);
    }
}
