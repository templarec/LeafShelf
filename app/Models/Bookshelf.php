<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookshelf extends Model
{
    use HasFactory;
    public function rooms(){
        return $this->belongsTo(Room::class, 'room_id');
    }
    public function shelves(){
        return $this->hasMany(Shelf::class);
    }
    static function getBookshelves($idRoom){
        return Bookshelf::where('room_id', $idRoom)->get();
    }
}
