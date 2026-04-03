<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ISBNQuery extends Controller
{
    public function fetch(Request $request)
    {
        $isbn = $this->normalizeIsbn($request->isbn);

        if (!$isbn) {
            return [
                [
                    'errorMessage' => 'Invalid ISBN',
                ]
            ];
        }

        $cacheKey = 'isbn_lookup_' . $isbn;
        $notFoundCacheKey = 'isbn_lookup_not_found_' . $isbn;

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return [
                [
                    'book' => $cached,
                    'cached' => true,
                    'stored' => 'cache',
                ]
            ];
        }

        if (Cache::get($notFoundCacheKey)) {
            return [
                [
                    'errorMessage' => 'Book not found',
                    'cached' => true,
                    'stored' => 'cache',
                ]
            ];
        }

        $persisted = $this->getPersistedLookup($isbn);

        if ($persisted) {
            if (!empty($persisted['not_found'])) {
                Cache::put($notFoundCacheKey, true, now()->addDay());

                return [
                    [
                        'errorMessage' => 'Book not found',
                        'cached' => true,
                        'stored' => 'db',
                    ]
                ];
            }

            if (!empty($persisted['payload'])) {
                Cache::put($cacheKey, $persisted['payload'], now()->addDays(30));

                return [
                    [
                        'book' => $persisted['payload'],
                        'cached' => true,
                        'stored' => 'db',
                    ]
                ];
            }
        }

        $book = $this->fetchFromGoogleBooks($isbn)
            ?? $this->fetchFromOpenLibrary($isbn)
            ?? $this->fetchFromIsbnDb($isbn);

        if (!$book) {
            Cache::forget($cacheKey);
            Cache::put($notFoundCacheKey, true, now()->addDay());
            $this->persistLookup($isbn, null, true);

            return [
                [
                    'errorMessage' => 'Book not found',
                ]
            ];
        }

        Cache::forget($notFoundCacheKey);
        Cache::put($cacheKey, $book, now()->addDays(30));
        $this->persistLookup($isbn, $book, false);

        return [
            [
                'book' => $book,
            ]
        ];
    }
    public function clearCache(Request $request)
    {
        $isbn = $this->normalizeIsbn($request->isbn);

        if (!$isbn) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ISBN',
            ], 422);
        }

        Cache::forget('isbn_lookup_' . $isbn);
        Cache::forget('isbn_lookup_not_found_' . $isbn);
        $this->deletePersistedLookup($isbn);

        return response()->json([
            'success' => true,
            'message' => 'ISBN cache cleared',
        ]);
    }
    protected function getPersistedLookup(string $isbn): ?array
    {
        if (!Schema::hasTable('isbn_lookups')) {
            return null;
        }

        $row = DB::table('isbn_lookups')
            ->where('isbn', $isbn)
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'payload' => $row->payload ? json_decode($row->payload, true) : null,
            'not_found' => (bool) $row->not_found,
        ];
    }

    protected function persistLookup(string $isbn, ?array $payload, bool $notFound): void
    {
        if (!Schema::hasTable('isbn_lookups')) {
            return;
        }

        DB::table('isbn_lookups')->updateOrInsert(
            ['isbn' => $isbn],
            [
                'payload' => $payload ? json_encode($payload) : null,
                'not_found' => $notFound,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function deletePersistedLookup(string $isbn): void
    {
        if (!Schema::hasTable('isbn_lookups')) {
            return;
        }

        DB::table('isbn_lookups')
            ->where('isbn', $isbn)
            ->delete();
    }
    protected function normalizeIsbn($value): ?string
    {
        $isbn = strtoupper(preg_replace('/[^0-9X]/', '', (string) $value));

        if ($isbn === '') {
            return null;
        }

        if (strlen($isbn) !== 10 && strlen($isbn) !== 13) {
            return null;
        }

        return $isbn;
    }

    protected function fetchFromGoogleBooks(string $isbn): ?array
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $isbn,
            'maxResults' => 1,
        ]);

        if (!$response->ok()) {
            return null;
        }

        $items = $response->json('items') ?? [];
        $volumeInfo = $items[0]['volumeInfo'] ?? null;

        if (!$volumeInfo) {
            return null;
        }

        return $this->normalizeBookPayload([
            'title' => $volumeInfo['title'] ?? null,
            'title_long' => $volumeInfo['subtitle'] ?? null
                ? trim(($volumeInfo['title'] ?? '') . ': ' . $volumeInfo['subtitle'])
                : ($volumeInfo['title'] ?? null),
            'publisher' => $volumeInfo['publisher'] ?? null,
            'pages' => $volumeInfo['pageCount'] ?? null,
            'image' => $volumeInfo['imageLinks']['thumbnail']
                ?? $volumeInfo['imageLinks']['smallThumbnail']
                ?? null,
            'authors' => $volumeInfo['authors'] ?? [],
        ]);
    }

    protected function fetchFromOpenLibrary(string $isbn): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'LeafShelf/1.0 (berninize@gmail.com)',
        ])->get('https://openlibrary.org/api/books', [
                    'bibkeys' => 'ISBN:' . $isbn,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);

        if (!$response->ok()) {
            return null;
        }

        $payload = $response->json();
        $book = $payload['ISBN:' . $isbn] ?? null;

        if (!$book) {
            return null;
        }

        return $this->normalizeBookPayload([
            'title' => $book['title'] ?? null,
            'title_long' => $book['subtitle'] ?? null
                ? trim(($book['title'] ?? '') . ': ' . $book['subtitle'])
                : ($book['title'] ?? null),
            'publisher' => $book['publishers'][0]['name'] ?? null,
            'pages' => $book['number_of_pages'] ?? null,
            'image' => $book['cover']['large']
                ?? $book['cover']['medium']
                ?? $book['cover']['small']
                ?? null,
            'authors' => collect($book['authors'] ?? [])->pluck('name')->filter()->values()->all(),
        ]);
    }

    protected function fetchFromIsbnDb(string $isbn): ?array
    {
        $key = env('ISBNdb_KEY');

        if (!$key) {
            return null;
        }

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => $key,
        ])->get("https://api2.isbndb.com/book/{$isbn}");

        if (!$response->ok()) {
            return null;
        }

        $book = $response->json('book');

        if (!$book) {
            return null;
        }

        return $this->normalizeBookPayload([
            'title' => $book['title'] ?? null,
            'title_long' => $book['title_long'] ?? ($book['title'] ?? null),
            'publisher' => $book['publisher'] ?? null,
            'pages' => $book['pages'] ?? null,
            'image' => $book['image'] ?? null,
            'authors' => $book['authors'] ?? [],
        ]);
    }

    protected function normalizeBookPayload(array $data): ?array
    {
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            return null;
        }

        $authors = collect($data['authors'] ?? [])
            ->map(fn($author) => trim((string) $author))
            ->filter()
            ->values()
            ->all();

        return [
            'title' => $title,
            'title_long' => trim((string) ($data['title_long'] ?? $title)) ?: $title,
            'publisher' => trim((string) ($data['publisher'] ?? '')) ?: null,
            'pages' => isset($data['pages']) ? (int) $data['pages'] : null,
            'image' => trim((string) ($data['image'] ?? '')) ?: null,
            'authors' => $authors,
        ];
    }
}