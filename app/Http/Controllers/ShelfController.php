<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuildingCollection;

use App\Http\Resources\BookshelfResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\BuildingResource;
use App\Http\Resources\ShelfResource;

use App\Models\Bookshelf;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShelfController extends Controller
{
    public function getShelves(Request $request)
    {
        $response = Shelf::getShelves((int) $request->idBookshelf);

        return response()->json($response);
    }

    public function addShelf(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'bookshelf_id' => 'required|integer|exists:bookshelves,id',
        ]);

        $shelf = Shelf::create([
            'name' => $data['name'],
            'bookshelf_id' => $data['bookshelf_id'],
        ]);

        return response()->json([
            'success' => true,
            'shelf' => $shelf,
        ], 201);
    }

    public function getPosKey(Request $request)
    {
        if (!str_starts_with($request->keyRip, 'shelf-')) {
            return response()->json([
                'success' => false,
                'message' => 'Chiave ripiano non valida',
            ], 400);
        }

        $id = (int) str_replace('shelf-', '', $request->keyRip);

        $shelf = Shelf::query()
            ->with('bookshelf.room.building')
            ->find($id);

        if (!$shelf) {
            return response()->json([
                'success' => false,
                'message' => 'Ripiano non trovato',
            ], 404);
        }

        return ShelfResource::make($shelf);
    }

    public function getPosKeyGlobal(Request $request)
    {
        $key = $request->keyRip;

        $map = [
            'building-' => [Building::class, BuildingResource::class, true, 'Building not found'],
            'room-' => [Room::class, RoomResource::class, true, 'Room not found'],
            'bookshelf-' => [Bookshelf::class, BookshelfResource::class, true, 'Bookshelf not found'],
            'shelf-' => [Shelf::class, ShelfResource::class, false, 'Shelf not found'],
        ];

        foreach ($map as $prefix => [$modelClass, $resourceClass, $selectable, $notFoundMessage]) {
            if (str_starts_with($key, $prefix)) {
                $id = (int) substr($key, strlen($prefix));

                $model = $modelClass::find($id);

                if (!$model) {
                    return response()->json([
                        'message' => $notFoundMessage,
                    ], 404);
                }

                $resource = $resourceClass::make($model);

                if ($selectable) {
                    $resource->selectable(true);
                }

                return $resource;
            }
        }

        return response()->json([
            'message' => 'Chiave non valida',
        ], 400);
    }
    public function renameShelf(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:shelves,id',
            'name' => 'required|string|max:255',
        ]);

        $shelf = Shelf::findOrFail($data['id']);
        $shelf->name = $data['name'];
        $shelf->save();

        return response()->json([
            'success' => true,
            'shelf' => $shelf,
        ], 200);
    }
    public function delItem(Request $request)
    {
        try {
            $allowedModels = [
                Building::class,
                Room::class,
                Bookshelf::class,
                Shelf::class,
            ];

            $model = $request->model;

            if (!in_array($model, $allowedModels, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modello non consentito',
                ], 400);
            }

            $item = $model::find($request->id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Elemento non trovato',
                ], 404);
            }

            $hasBooks = match ($model) {
                Shelf::class => $item->books()->exists(),
                Bookshelf::class => $item->shelves()->whereHas('books')->exists(),
                Room::class => $item->bookshelves()->whereHas('shelves.books')->exists(),
                Building::class => $item->rooms()->whereHas('bookshelves.shelves.books')->exists(),
                default => false,
            };

            if ($hasBooks) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossibile rimuovere questa posizione perché contiene libri o porterebbe alla loro eliminazione.',
                ], 422);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cancellato con successo',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTree(Request $request)
    {
        $selectable = $request->boolean('selectable');

        $buildings = Building::query()
            ->with([
                'rooms.bookshelves.shelves' => function ($query) {
                    $query->withCount('books');
                },
            ])
            ->get();

        $buildings->each(function ($building) {
            $buildingBooksCount = 0;

            $building->rooms->each(function ($room) use (&$buildingBooksCount) {
                $roomBooksCount = 0;

                $room->bookshelves->each(function ($bookshelf) use (&$roomBooksCount) {
                    $bookshelfBooksCount = $bookshelf->shelves->sum(function ($shelf) {
                        return (int) ($shelf->books_count ?? 0);
                    });

                    $bookshelf->setAttribute('books_count', $bookshelfBooksCount);
                    $roomBooksCount += $bookshelfBooksCount;
                });

                $room->setAttribute('books_count', $roomBooksCount);
                $buildingBooksCount += $roomBooksCount;
            });

            $building->setAttribute('books_count', $buildingBooksCount);
        });

        return BuildingCollection::make($buildings)->selectable($selectable);
    }
    public function books($id)
    {
        $shelf = Shelf::query()
            ->with([
                'books.authors',
                'bookshelf.room.building',
            ])
            ->findOrFail($id);

        $location = [
            'building' => $shelf->bookshelf?->room?->building?->name,
            'room' => $shelf->bookshelf?->room?->name,
            'bookshelf' => $shelf->bookshelf?->name,
            'shelf' => $shelf->name,
        ];

        $books = $this->transformBooks($shelf->books);

        return Inertia::render('ShelfBooks', [
            'shelf' => [
                'id' => $shelf->id,
                'name' => $shelf->name,
                'location' => $location,
            ],
            'books' => $books,
        ]);
    }

    private function transformBooks($books)
    {
        return $books->map(function ($book) {
            return [
                'id' => $book->id,
                'isbn' => $book->ISBN,
                'title' => $book->title,
                'publisher' => $book->publisher,
                'pages' => $book->pages,
                'img' => $book->img,
                'authors' => $book->authors->map(function ($author) {
                    return [
                        'name' => $author->name,
                        'surname' => $author->surname,
                    ];
                })->values(),
            ];
        })->values();
    }
}