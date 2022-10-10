<?php

    namespace App\Http\Controllers;

    use App\Http\Resources\BookCollection;
    use App\Http\Resources\ShelfCollection;
    use App\Http\Resources\AuthorCollection;
    use App\Http\Resources\ShelfResource;
    use App\Models\Author;
    use App\Models\Book;
    use App\Models\Building;
    use App\Models\Shelf;
    use Illuminate\Http\Request;

    class BookController extends Controller
    {
        public function getLocation(Request $request)
        {
            return ShelfResource::make(Shelf::find($request->idShelf));
        }

        public function saveBook(Request $request)
        {
            $book = Book::create([
                'ISBN' => $request->isbn,
                'title' => $request->title,
                'pubisher' => $request->publisher,
                'pages' => $request->pages,
                'img' => $request->img,
                'shelf_id' => $request->shelfId,
            ]);
//        return response()->json($request->authors);
            foreach ($request->authors as $autori) {
                $author = Author::findAuthor($autori['nome'], $autori['cognome']);
            }

            if ($author) {
                $book->authors()->attach($author->id);
            } else {
                $createAuthor = Author::create([
                    'name' => $autori['nome'],
                    'surname' => $autori['cognome'],
                ]);
                $book->authors()->attach($createAuthor->id);
            }
        }

        public function all()
        {
            return new BookCollection(Book::all());

        }

        public function search(Request $request)
        {
            $searchterm = $request->searchtxt;

            return new BookCollection(Book::where('title', 'like', "%{$searchterm}%")->get());
        }

    }
