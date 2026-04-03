<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $padova = [
            'Soggiorno',
            'Ingresso',
            'Corridoio',
            'Stanza Singola 1',
            'Stanza Matrimoniale',
            'Stanza Singola 2',
        ];
        $udine = [
            'Soggiorno',
            'Studio'
        ];
        $padovaId = DB::table('buildings')->where('name', 'PADOVA')->value('id');
        $udineId = DB::table('buildings')->where('name', 'UDINE')->value('id');
        foreach ($padova as $item) {
            DB::table('rooms')->insert([
                'name' => $item,
                'building_id' => $padovaId,

            ]);
        }
        foreach ($udine as $item) {
            DB::table('rooms')->insert([
                'key' => 'room-1',
                'name' => 'Soggiorno',
                'building_id' => 1,
            ]);
        }
    }
}
