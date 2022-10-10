<?php

namespace App\Http\Controllers;

use App\Models\Bookshelf;
use Illuminate\Http\Request;

class BookshelfController extends Controller
{
  public function getBookshelves(Request $request){
      $response = Bookshelf::getBookshelves($request->idRoom);
      return response()->json($response);
  }
}
