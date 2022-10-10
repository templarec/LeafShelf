<?php

    use Illuminate\Foundation\Application;
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
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::get('/ins-libro/{idShelf}', function () {
        return Inertia::render('ins-libro');
    })->middleware(['auth', 'verified'])->name('ins-libro');

    Route::get('/ins-pos', function () {
        return Inertia::render('ins-posizione');
    })->middleware(['auth', 'verified'])->name('ins-pos');
    Route::get('/consultazione', function () {
        return Inertia::render('consultazione');
    })->middleware(['auth', 'verified'])->name('consultazione');

    Route::get('/search', [\App\Http\Controllers\ISBNQuery::class, 'fetch'])->middleware(['auth', 'verified']);
    Route::get('/get-buildings', [\App\Http\Controllers\BuildingController::class, 'getBuildings'])->middleware(
        ['auth', 'verified']
    );
    Route::get('/get-rooms', [\App\Http\Controllers\RoomController::class, 'getRooms'])->middleware(['auth', 'verified']
    );
    Route::get('/get-bookshelves', [\App\Http\Controllers\BookshelfController::class, 'getBookshelves'])->middleware(
        ['auth', 'verified']
    );
    Route::get('/get-shelves', [\App\Http\Controllers\ShelfController::class, 'getShelves'])->middleware(
        ['auth', 'verified']
    );
    Route::get('/get-location', [\App\Http\Controllers\BookController::class, 'getLocation'])->middleware(
        ['auth', 'verified']
    );
    Route::post('/save-book', [\App\Http\Controllers\BookController::class, 'saveBook'])->middleware(
        ['auth', 'verified']
    );
    Route::get('/all-books', [\App\Http\Controllers\BookController::class, 'all'])->middleware(['auth', 'verified']);
    Route::get('/search', [\App\Http\Controllers\BookController::class, 'search'])->middleware(['auth', 'verified']);
    require __DIR__.'/auth.php';
