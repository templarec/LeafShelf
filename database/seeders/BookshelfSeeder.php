<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BookshelfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bs = [
            'Mobile TV' => ['Soggiorno'],
            'Sopra scala' => ['Ingresso'],
            'Libreria' => [
                'Soggiorno',
                'Ingresso',
                'Corridoio',
                'Stanza Singola 1',
                'Stanza Matrimoniale',
                'Stanza Singola 2'
            ]
        ];
        foreach ($bs as $lib => $rooms) {
            foreach ($rooms as $roomName) {

                $roomId = DB::table('rooms')
                    ->where('name', $roomName)
                    ->value('id');

                if ($roomId) {
                    DB::table('bookshelves')->insert([
                        'name' => $lib,
                        'room_id' => $roomId
                    ]);
                }
            }
        }
    }
}
