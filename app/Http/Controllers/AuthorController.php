<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthorCollection;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function searchAutori(Request $request)
    {
        $data = $request->validate([
            'searchtxt' => 'nullable|string|max:255',
        ]);

        $searchterm = $data['searchtxt'] ?? '';

        return new AuthorCollection(
            Author::query()
                ->where(function ($query) use ($searchterm) {
                    $query->where('name', 'like', "%{$searchterm}%")
                        ->orWhere('surname', 'like', "%{$searchterm}%");
                })
                ->orderBy('surname', 'asc')
                ->orderBy('name', 'asc')
                ->get()
        );
    }
}