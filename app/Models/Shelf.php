<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasFactory;
    public function books(){
        return $this->hasMany(Book::class);
    }
    public function bookshelves(){
        return $this->belongsTo(Bookshelf::class, 'bookshelf_id');
    }
    static function getShelves($idBookshelf){
        return Shelf::where('bookshelf_id', $idBookshelf)->get();
    }
}
