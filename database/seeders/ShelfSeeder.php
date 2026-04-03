<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ShelfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
{
    $sh = [
        'Primo ripiano',
        'Secondo ripiano',
        'Terzo ripiano'
    ];

    $bookshelves = DB::table('bookshelves')->get();

    foreach ($bookshelves as $bookshelf) {
        foreach ($sh as $rip) {
            DB::table('shelves')->insert([
                'name' => $rip,
                'bookshelf_id' => $bookshelf->id
            ]);
        }
    }
}
}
