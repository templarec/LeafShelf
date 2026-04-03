<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/books/create/{shelf}', function () {
        return Inertia::render('BookCreate');
    })->name('books.create');

    Route::get('/books', function () {
        return Inertia::render('BooksIndex');
    })->name('books.index');

    Route::get('/authors', function () {
        return Inertia::render('AuthorsIndex');
    })->name('authors.index');

    Route::get('/locations', function () {
        return Inertia::render('LocationsManager');
    })->name('locations.index');


    Route::get('/books/{id}', [App\Http\Controllers\BookController::class, 'show'])
        ->name('book.show');

    Route::put('/books/{id}', [App\Http\Controllers\BookController::class, 'update'])
        ->name('book.update');

    Route::get('/shelves/{id}/books', [App\Http\Controllers\ShelfController::class, 'books'])
        ->name('shelf.books.index');

    Route::prefix('api')->group(function () {
        Route::get('/isbn-lookup', [\App\Http\Controllers\ISBNQuery::class, 'fetch'])
            ->name('api.isbn.lookup');

        Route::get('/buildings', [\App\Http\Controllers\BuildingController::class, 'getBuildings'])
            ->name('api.buildings.index');
        Route::post('/buildings', [\App\Http\Controllers\BuildingController::class, 'addBuilding'])
            ->name('api.buildings.store');

        Route::get('/rooms', [\App\Http\Controllers\RoomController::class, 'getRooms'])
            ->name('api.rooms.index');
        Route::post('/rooms', [\App\Http\Controllers\RoomController::class, 'addRoom'])
            ->name('api.rooms.store');

        Route::get('/bookshelves', [\App\Http\Controllers\BookshelfController::class, 'getBookshelves'])
            ->name('api.bookshelves.index');
        Route::post('/bookshelves', [\App\Http\Controllers\BookshelfController::class, 'addBS'])
            ->name('api.bookshelves.store');

        Route::get('/shelves', [\App\Http\Controllers\ShelfController::class, 'getShelves'])
            ->name('api.shelves.index');
        Route::post('/shelves', [\App\Http\Controllers\ShelfController::class, 'addShelf'])
            ->name('api.shelves.store');
        Route::patch('/shelves/rename', [App\Http\Controllers\ShelfController::class, 'renameShelf'])
            ->name('api.shelves.rename');

        Route::get('/books/location', [\App\Http\Controllers\BookController::class, 'getLocation'])
            ->name('api.books.location');
        Route::post('/books', [\App\Http\Controllers\BookController::class, 'saveBook'])
            ->name('api.books.store');
        Route::get('/books/search', [\App\Http\Controllers\BookController::class, 'search'])
            ->name('api.books.search');
        Route::get('/books/recent', [\App\Http\Controllers\BookController::class, 'lastBooks'])
            ->name('api.books.recent');

        Route::get('/authors/search', [\App\Http\Controllers\AuthorController::class, 'searchAutori'])
            ->name('api.authors.search');

        Route::get('/locations/tree', [\App\Http\Controllers\ShelfController::class, 'getTree'])
            ->name('api.locations.tree');
        Route::get('/locations/key', [\App\Http\Controllers\ShelfController::class, 'getPosKey'])
            ->name('api.locations.key');
        Route::get('/locations/key-global', [\App\Http\Controllers\ShelfController::class, 'getPosKeyGlobal'])
            ->name('api.locations.key-global');
        Route::delete('/locations/item', [\App\Http\Controllers\ShelfController::class, 'delItem'])
            ->name('api.locations.delete');
    });
});
require __DIR__ . '/auth.php';
