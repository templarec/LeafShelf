<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    public function buildings(){
        return $this->belongsTo(Building::class, 'building_id');
    }
    public function bookshelves(){
        return $this->hasMany(Bookshelf::class);
    }

    static function getRooms($idBuilding) {
        return Room::where('building_id', $idBuilding)->get();
    }
}
