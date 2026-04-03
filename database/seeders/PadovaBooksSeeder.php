<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Building;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Author;
use Illuminate\Support\Facades\DB;

class PadovaBooksSeeder extends Seeder
{
    public function run(): void
    {
        $building = Building::query()
            ->where('name', 'PADOVA')
            ->with('rooms.bookshelves.shelves')
            ->first();

        if (!$building) {
            $this->command->error("Casa PADOVA non trovata.");
            return;
        }

        $shelves = $building->rooms
            ->flatMap(fn($room) => $room->bookshelves)
            ->flatMap(fn($bookshelf) => $bookshelf->shelves)
            ->values();

        if ($shelves->isEmpty()) {
            $this->command->error("Nessun ripiano trovato sotto PADOVA.");
            return;
        }



        $publishers = [
            'Mondadori',
            'Einaudi',
            'Feltrinelli',
            'Adelphi',
            'Sellerio',
            'Bompiani',
            'Rizzoli',
            'Garzanti',
            'Laterza',
            'Il Mulino',
            'Fazi',
            'Marsilio',
            'Minimum Fax',
            'Neri Pozza',
            'Ponte alle Grazie',
            'NN Editore',
            'Sur',
            'HarperCollins Italia',
            'Sperling & Kupfer',
            'Bao Publishing',
        ];

        $italianFirstNames = [
            'Andrea',
            'Marco',
            'Luca',
            'Paolo',
            'Francesco',
            'Giulia',
            'Anna',
            'Sara',
            'Elena',
            'Marta',
            'Chiara',
            'Davide',
            'Alessandro',
            'Giorgio',
            'Federica',
            'Valentina',
            'Matteo',
            'Simone',
            'Beatrice',
            'Irene',
        ];

        $italianLastNames = [
            'Rinaldi',
            'Moretti',
            'Ferri',
            'Conti',
            'Greco',
            'Marini',
            'Barbieri',
            'Galli',
            'Lombardi',
            'Serra',
            'De Santis',
            'Bianco',
            'Rossetti',
            'Villa',
            'Pellegrini',
            'Orlando',
            'Coppola',
            'Leone',
            'Parisi',
            'Testa',
        ];

        $foreignAuthors = [
            'Haruki Murakami',
            'Margaret Atwood',
            'Kazuo Ishiguro',
            'Donna Tartt',
            'Ian McEwan',
            'Paul Auster',
            'Philip Roth',
            'David Foster Wallace',
            'Ursula K. Le Guin',
            'Toni Morrison',
            'Julian Barnes',
            'Sally Rooney',
            'Zadie Smith',
            'Milan Kundera',
            'Amélie Nothomb',
            'Javier Marías',
            'Elena Ferrante',
            'Roberto Bolaño',
            'John Williams',
            'Cormac McCarthy',
        ];

        $titlePrefixes = [
            'L\'ombra di',
            'Il segreto di',
            'La casa di',
            'L\'estate di',
            'Le città di',
            'Il silenzio di',
            'La memoria di',
            'Il giardino di',
            'Le stanze di',
            'La biblioteca di',
            'Il tempo di',
            'Le forme di',
            'Il canto di',
            'La misura di',
            'Il confine di',
            'Le ore di',
            'Il nome di',
            'La teoria di',
            'L\'inverno di',
            'Il rumore di',
        ];

        $titleSubjects = [
            'vetro',
            'polvere',
            'neve',
            'ferro',
            'carta',
            'pioggia',
            'cenere',
            'vento',
            'luce',
            'notte',
            'acqua',
            'sale',
            'ombra',
            'silenzio',
            'radici',
            'mare',
            'rami',
            'strade',
            'specchi',
            'stelle',
            'muri',
            'porte',
            'archivi',
            'giardini',
            'mappe',
        ];

        $titleSuffixes = [
            null,
            'perduto',
            'interrotto',
            'segreto',
            'invisibile',
            'sommerso',
            'lontano',
            'spezzato',
            'ritrovato',
            'sottile',
            'infinito',
            'quieto',
            'dimenticato',
        ];



        $targetBooks = 3000;

        $usedIsbns = Book::query()
            ->pluck('ISBN')
            ->filter()
            ->map(fn($isbn) => (string) $isbn)
            ->flip()
            ->all();

        $weightedShelfIds = [];
        foreach ($shelves as $shelf) {
            // Peso casuale per avere distribuzione variabile e non uniforme
            // Alcuni ripiani saranno molto pieni, altri meno
            $weight = random_int(2, 14);

            // Piccolo boost random per alcuni ripiani “centrali”
            if (random_int(1, 100) <= 22) {
                $weight += random_int(6, 18);
            }

            for ($i = 0; $i < $weight; $i++) {
                $weightedShelfIds[] = $shelf->id;
            }
        }

        $books = [];

        for ($i = 0; $i < $targetBooks; $i++) {
            $shelfId = Arr::random($weightedShelfIds);

            $author = random_int(1, 100) <= 68
                ? $this->fakeItalianAuthor($italianFirstNames, $italianLastNames)
                : $this->splitAuthorName(Arr::random($foreignAuthors));

            $title = $this->fakeRealisticTitle(
                $titlePrefixes,
                $titleSubjects,
                $titleSuffixes
            );

            $publisher = Arr::random($publishers);
            $pages = random_int(96, 920);
            $isbn = $this->generateUniqueValidIsbn13($usedIsbns);
            $createdAt = now();
            $updatedAt = now();

            $books[] = [
                'ISBN' => $isbn,
                'title' => $title,
                'publisher' => $publisher,
                'pages' => $pages,
                'shelf_id' => $shelfId,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                '_author_name' => $author['name'],
                '_author_surname' => $author['surname'],
            ];
        }

        foreach (array_chunk($books, 100) as $chunk) {
            foreach ($chunk as $row) {
                $authorName = $row['_author_name'];
                $authorSurname = $row['_author_surname'];

                unset($row['_author_name'], $row['_author_surname']);

                $book = Book::query()->create($row);

                $author = Author::query()->firstOrCreate([
                    'name' => $authorName,
                    'surname' => $authorSurname,
                ]);

                $book->authors()->attach($author->id);
            }
        }

        $this->command->info("Creati {$targetBooks} libri realistici distribuiti nei ripiani di PADOVA.");
    }

    private function fakeItalianAuthor(array $firstNames, array $lastNames): array
    {
        return [
            'name' => Arr::random($firstNames),
            'surname' => Arr::random($lastNames),
        ];
    }

    private function splitAuthorName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (!$parts || count($parts) === 1) {
            return [
                'name' => $fullName,
                'surname' => 'Autore',
            ];
        }

        $name = array_shift($parts);
        $surname = implode(' ', $parts);

        return [
            'name' => $name,
            'surname' => $surname,
        ];
    }

    private function fakeRealisticTitle(array $prefixes, array $subjects, array $suffixes): string
    {
        $prefix = Arr::random($prefixes);
        $subject = Arr::random($subjects);
        $suffix = Arr::random($suffixes);

        $title = $prefix . ' ' . $subject;

        if ($suffix) {
            $title .= ' ' . $suffix;
        }

        return Str::title($title);
    }
    private function generateUniqueValidIsbn13(array &$usedIsbns): string
    {
        do {
            $isbn = $this->generateValidIsbn13();
        } while (isset($usedIsbns[$isbn]));

        $usedIsbns[$isbn] = true;

        return $isbn;
    }
    private function generateValidIsbn13(): string
    {
        // Prefissi realistici ISBN Bookland
        $prefix = Arr::random(['978', '979']);

        // Gruppo 88 = area linguistica italiana, più realistico per questo seed
        $body = $prefix . '88';

        // Completa fino a 12 cifre
        while (strlen($body) < 12) {
            $body .= (string) random_int(0, 9);
        }

        $checkDigit = $this->isbn13CheckDigit($body);

        return $body . $checkDigit;
    }

    private function isbn13CheckDigit(string $first12Digits): int
    {
        $sum = 0;

        foreach (str_split($first12Digits) as $index => $digit) {
            $n = (int) $digit;
            $sum += $index % 2 === 0 ? $n : $n * 3;
        }

        return (10 - ($sum % 10)) % 10;
    }
}