<?php

    namespace Database\Seeders;

    use App\Models\Bookshelf;
    use App\Models\Building;
    use App\Models\Room;
    use Illuminate\Database\Console\Seeds\WithoutModelEvents;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;

    class BuildingSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            $aaa = [
                [
                    'name' => 'PADOVA',
                    'key' => '0',
                    'rooms' => [
                        [
                            'name' => 'Soggiorno',
                            'key' => '0',
                            'bookshelves' => [
                                [
                                    'name' => 'Mobile Tv',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Ingresso',
                            'key' => '1',
                            'bookshelves' => [
                                [
                                    'name' => 'Sopra Scala',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Camera Lorenzo',
                            'key' => '2',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Camera Matrimoniale',
                            'key' => '3',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Camera Ospiti',
                            'key' => '4',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Sottoscala',
                            'key' => '5',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Ingresso Taverna',
                            'key' => '6',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Cucina Taverna',
                            'key' => '7',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Camera Taverna',
                            'key' => '8',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Corridoio',
                            'key' => '9',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],

                    ],
                ],
                [
                    'name' => 'UDINE',
                    'key' => '1',
                    'rooms' => [
                        [
                            'name' => 'Soggiorno',
                            'key' => '0',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Studio',
                            'key' => '1',
                            'bookshelves' => [
                                [
                                    'name' => 'Libreria',
                                    'key' => '0',
                                    'shelves' => [
                                        [
                                            'name' => 'Primo ripiano',
                                            'key' => '0',
                                        ],
                                        [
                                            'name' => 'Secondo ripiano',
                                            'key' => '1',
                                        ],
                                        [
                                            'name' => 'Terzo ripiano',
                                            'key' => '2',
                                        ],
                                        [
                                            'name' => 'Quarto ripiano',
                                            'key' => '3',
                                        ],
                                        [
                                            'name' => 'Quinto ripiano',
                                            'key' => '4',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            foreach ($aaa as $building) {
                DB::table('buildings')->insert([
                    'name' => $building['name'],
                    'key' => $building['key'],
                ]);
                foreach ($building['rooms'] as $room) {
                    $idbuilding = Building::where('key', $building['key'])->first();
                    DB::table('rooms')->insert([
                        'name' => $room['name'],
                        'key' => $building['key'].'-'.$room['key'],
                        'building_id' => $idbuilding->id,
                    ]);
                    foreach ($room['bookshelves'] as $bookshelf) {
                        $idroom = Room::where('key', $building['key'].'-'.$room['key'])->first();
                        DB::table('bookshelves')->insert([
                            'name' => $bookshelf['name'],
                            'key' => $building['key'].'-'.$room['key'].'-'.$bookshelf['key'],
                            'room_id' => $idroom->id,
                        ]);
                        foreach ($bookshelf['shelves'] as $shelf) {
                            $idbs = Bookshelf::where(
                                'key',
                                $building['key'].'-'.$room['key'].'-'.$bookshelf['key']
                            )->first();
                            DB::table('shelves')->insert([
                                'name' => $shelf['name'],
                                'key' => $building['key'].'-'.$room['key'].'-'.$bookshelf['key'].'-'.$shelf['key'],
                                'bookshelf_id' => $idbs->id,
                            ]);
                        }
                    }
                }

            }


        }
    }
