<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;
    protected $guarded =  [];
    public function books(){
        return $this->belongsToMany(Book::class, 'books_authors');
    }
    static function findAuthor($name, $surname){
        return self::where('name', $name)
            ->where('surname', $surname)->first();

    }
}
