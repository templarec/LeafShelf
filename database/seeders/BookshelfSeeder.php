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
          'Mobile TV' => [1],
          'Sopra scala' => [2],
          'Libreria' => [3,4,5,6,7,8,9,10,11,12]

        ];
        foreach ($bs as $lib => $index ){
            foreach ($index as $id){
                DB::table('bookshelves')->insert([
                   'name' => $lib,
                   'room_id' => $id
                ]);
            }
        }
    }
}
