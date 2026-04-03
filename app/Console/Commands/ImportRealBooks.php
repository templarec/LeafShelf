<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use App\Models\Author;
use App\Models\Shelf;

class ImportRealBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:import-real {--count=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int) $this->option('count');

        $this->info("Importing {$count} real books...");

        $candidates = $this->fetchCandidates($count);

        $this->info("Fetched " . count($candidates) . " candidates from API");

        $books = $this->normalizeBooks($candidates);

        $this->info("After normalization: " . count($books) . " books");

        $this->persistBooks($books);

        $this->info("Import completed.");

        return Command::SUCCESS;
    }

    protected function fetchCandidates(int $count): array
    {
        $results = [];
        $attempt = 1;

        $bar = $this->output->createProgressBar($count);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | elapsed: %elapsed:6s% | remaining: %remaining:6s%');
        $bar->start();

        while (count($results) < $count && $attempt < 10) {

            $queries = [
                'fiction',
                'novel',
                'literature',
                'mystery',
                'fantasy',
                'science fiction',
                'historical fiction',
                'classic literature',
                'philosophy novel',
                'thriller',
            ];

            $query = $queries[array_rand($queries)];
            $randomPage = random_int(1, 50);

            $response = Http::withHeaders([
                'User-Agent' => 'LeafShelf/1.0 (berninize@gmail.com)'
            ])->get('https://openlibrary.org/search.json', [
                        'q' => $query,
                        'limit' => 100,
                        'page' => $randomPage,
                    ]);


            if (!$response->ok()) {
                $this->error("OpenLibrary request failed on attempt {$attempt}");
                break;
            }

            $docs = $response->json()['docs'] ?? [];

            foreach ($docs as $doc) {
                if (
                    empty($doc['cover_i']) ||
                    empty($doc['cover_edition_key']) ||
                    empty($doc['title']) ||
                    empty($doc['author_name'])
                ) {
                    continue;
                }

                $edition = $this->fetchEditionDetails($doc['cover_edition_key']);

                if (!$edition) {
                    continue;
                }

                $isbn = $this->extractValidIsbnFromEdition($edition);

                if (!$isbn) {
                    continue;
                }

                $results[] = [
                    'isbn' => $isbn,
                    'title' => $doc['title'],
                    'author_name' => $doc['author_name'][0],
                    'author_surname' => '',
                    'publisher' => $this->extractPublisherFromEdition($edition),
                    'pages' => $doc['number_of_pages_median'] ?? 200,
                    'img' => "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-L.jpg",
                ];
                $bar->advance();

                if (count($results) >= $count) {
                    break;
                }
            }

            $attempt++;
        }

        $bar->finish();
        $this->newLine();
        return $results;
    }

    protected function fetchEditionDetails(string $editionKey): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'LeafShelf/1.0 (berninize@gmail.com)'
        ])->get("https://openlibrary.org/books/{$editionKey}.json");

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    protected function extractValidIsbnFromEdition(array $edition): ?string
    {
        $candidates = array_merge(
            $edition['isbn_13'] ?? [],
            $edition['isbn_10'] ?? []
        );

        foreach ($candidates as $candidate) {
            $candidate = preg_replace('/[^0-9X]/', '', (string) $candidate);

            if (strlen($candidate) === 10 || strlen($candidate) === 13) {
                return $candidate;
            }
        }

        return null;
    }
    protected function extractPublisherFromEdition(array $edition): string
    {
        $publishers = $edition['publishers'] ?? [];

        if (empty($publishers)) {
            return 'Unknown';
        }

        $firstPublisher = $publishers[0];

        if (is_string($firstPublisher) && trim($firstPublisher) !== '') {
            return trim($firstPublisher);
        }

        if (is_array($firstPublisher) && !empty($firstPublisher['name'])) {
            return trim((string) $firstPublisher['name']);
        }

        return 'Unknown';
    }

    protected function normalizeBooks(array $candidates): array
    {
        $normalized = [];
        $seenIsbns = Book::query()
            ->pluck('ISBN')
            ->filter()
            ->map(fn($isbn) => (string) $isbn)
            ->flip()
            ->all();

        foreach ($candidates as $candidate) {
            $isbn = trim((string) ($candidate['isbn'] ?? ''));
            $title = trim((string) ($candidate['title'] ?? ''));
            $publisher = trim((string) ($candidate['publisher'] ?? ''));
            $pages = (int) ($candidate['pages'] ?? 0);
            $img = trim((string) ($candidate['img'] ?? ''));
            $fullAuthorName = trim((string) ($candidate['author_name'] ?? ''));

            if ($isbn === '' || $title === '' || $fullAuthorName === '' || $img === '') {
                continue;
            }

            if (isset($seenIsbns[$isbn])) {
                continue;
            }

            $author = $this->splitAuthorName($fullAuthorName);

            $normalized[] = [
                'isbn' => $isbn,
                'title' => $title,
                'publisher' => $publisher !== '' ? $publisher : 'Unknown',
                'pages' => $pages > 0 ? $pages : 200,
                'img' => $img,
                'author_name' => $author['name'],
                'author_surname' => $author['surname'],
            ];

            $seenIsbns[$isbn] = true;
        }

        return $normalized;
    }
    protected function splitAuthorName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (!$parts || count($parts) === 1) {
            return [
                'name' => $fullName,
                'surname' => '',
            ];
        }

        $name = array_shift($parts);
        $surname = implode(' ', $parts);

        return [
            'name' => $name,
            'surname' => $surname,
        ];
    }

    protected function persistBooks(array $books): void
    {
        $shelves = Shelf::query()->get();

        if ($shelves->isEmpty()) {
            $this->error('No shelves found.');
            return;
        }

        $imported = 0;
        $total = count($books);

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | elapsed: %elapsed:6s% | remaining: %remaining:6s%');
        $bar->start();

        foreach ($books as $data) {

            $shelf = $shelves->random();

            $book = Book::query()->create([
                'ISBN' => $data['isbn'],
                'title' => $data['title'],
                'publisher' => $data['publisher'],
                'pages' => $data['pages'],
                'img' => $data['img'],
                'shelf_id' => $shelf->id,
            ]);

            $author = Author::query()->firstOrCreate([
                'name' => $data['author_name'],
                'surname' => $data['author_surname'],
            ]);

            $book->authors()->syncWithoutDetaching([$author->id]);

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Persisted {$imported} books.");
    }
}
