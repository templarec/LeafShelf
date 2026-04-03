<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookCollection;
use App\Http\Resources\ShelfResource;
use App\Models\Author;
use App\Models\Book;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookController extends Controller
{
    public function getLocation(Request $request)
    {
        $shelf = Shelf::query()
            ->with('bookshelf.room.building')
            ->findOrFail($request->idShelf);

        $building = $shelf->bookshelf?->room?->building;
        $room = $shelf->bookshelf?->room;
        $bookshelf = $shelf->bookshelf;

        return response()->json([
            'data' => [
                [
                    'id' => $building?->id,
                    'key' => 'building-' . $building?->id,
                    'label' => $building?->name,
                    'icon' => 'pi pi-fw pi-home',
                    'data' => $building?->name,
                    'children' => [
                        [
                            'id' => $room?->id,
                            'key' => 'room-' . $room?->id,
                            'label' => $room?->name,
                            'icon' => 'pi pi-fw pi-stop',
                            'data' => $room?->name,
                            'children' => [
                                [
                                    'id' => $bookshelf?->id,
                                    'key' => 'bookshelf-' . $bookshelf?->id,
                                    'label' => $bookshelf?->name,
                                    'icon' => 'pi pi-fw pi-server',
                                    'data' => $bookshelf?->name,
                                    'children' => [
                                        [
                                            'id' => $shelf->id,
                                            'key' => 'shelf-' . $shelf->id,
                                            'label' => $shelf->name,
                                            'icon' => 'pi pi-fw pi-book',
                                            'data' => $shelf->name,
                                            'children' => [],
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
        ]);
    }

    public function saveBook(Request $request)
    {
        $data = $request->validate([
            'isbn' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'pages' => 'nullable|integer|min:1',
            'img' => 'nullable|string|max:2048',
            'shelfId' => 'required|integer',
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required_with:authors|string|max:255',
            'authors.*.surname' => 'required_with:authors|string|max:255',
        ]);

        DB::transaction(function () use ($data) {

            $book = Book::create([
                'ISBN' => $data['isbn'],
                'title' => $data['title'],
                'publisher' => $data['publisher'],
                'pages' => $data['pages'],
                'img' => $data['img'],
                'shelf_id' => $data['shelfId'],
            ]);

            foreach ($data['authors'] as $authorData) {

                $author = Author::firstOrCreate([
                    'name' => trim($authorData['name']),
                    'surname' => trim($authorData['surname']),
                ]);

                $book->authors()->syncWithoutDetaching($author->id);
            }
        });

        return response()->json([
            'success' => true
        ]);
    }


    public function search(Request $request)
    {
        $searchterm = trim($request->searchtxt ?? '');
        if ($searchterm === '') {
            return new BookCollection(collect());
        }

        $books = Book::query()
            ->with([
                'authors',
                'shelf.bookshelf.room.building',
            ])
            ->where(function ($query) use ($searchterm) {
                $query->where('title', 'like', "%{$searchterm}%")
                    ->orWhere('ISBN', 'like', "%{$searchterm}%")
                    ->orWhereHas('authors', function ($authorQuery) use ($searchterm) {
                        $authorQuery->where('name', 'like', "%{$searchterm}%")
                            ->orWhere('surname', 'like', "%{$searchterm}%");
                    });
            })
            ->limit(50)
            ->get();
        return new BookCollection($books);
    }

    public function lastBooks()
    {
        $books = Book::with('authors', 'shelf.bookshelf.room.building')
            ->latest()
            ->take(5)
            ->get();

        return new BookCollection($books);
    }
    public function show($id)
    {
        $book = Book::query()
            ->with([
                'authors',
                'shelf.bookshelf.room.building',
            ])
            ->findOrFail($id);

        return Inertia::render('BookShow', [
            'book' => [
                'id' => $book->id,
                'isbn' => $book->ISBN,
                'title' => $book->title,
                'publisher' => $book->publisher,
                'pages' => $book->pages,
                'img' => $book->img,
                'authors' => $book->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                        'surname' => $author->surname,
                    ];
                })->values(),
                'location' => [
                    'building' => $book->shelf?->bookshelf?->room?->building?->name,
                    'room' => $book->shelf?->bookshelf?->room?->name,
                    'bookshelf' => $book->shelf?->bookshelf?->name,
                    'shelf' => $book->shelf?->name,
                    'shelf_id' => $book->shelf?->id,
                ],
            ],
        ]);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'isbn' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'pages' => 'nullable|integer|min:1',
            'img' => 'nullable|string|max:2048',
            'authors' => 'nullable|array',
            'authors.*.name' => 'required_with:authors|string|max:255',
            'authors.*.surname' => 'required_with:authors|string|max:255',
        ]);

        $book = Book::query()->findOrFail($id);

        DB::transaction(function () use ($book, $data) {

            $book->update([
                'ISBN' => $data['isbn'],
                'title' => $data['title'],
                'publisher' => $data['publisher'],
                'pages' => $data['pages'],
                'img' => $data['img'],
            ]);

            if (isset($data['authors'])) {

                $authorIds = [];

                foreach ($data['authors'] as $authorData) {

                    $author = Author::firstOrCreate([
                        'name' => trim($authorData['name']),
                        'surname' => trim($authorData['surname']),
                    ]);

                    $authorIds[] = $author->id;
                }

                $book->authors()->sync($authorIds);
            }

        });

        return response()->json([
            'message' => 'Book updated successfully.',
        ]);
    }
}