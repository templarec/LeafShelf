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
           'Soggiorno', 'Ingresso', 'Corridoio','Camera Lorenzo', 'Camera matrimoniale', 'Camera ospiti', 'Sottoscala', 'Ingresso taverna', 'Camera taverna', 'Cucina taverna'
       ];
       $udine = [
           'Soggiorno', 'Studio'
       ];

       foreach ($padova as $item){
           DB::table('rooms')->insert([
              'name' => $item,
              'building_id' => 1
           ]);
       }
        foreach ($udine as $item){
            DB::table('rooms')->insert([
                'name' => $item,
                'building_id' => 2
            ]);
        }
    }
}
