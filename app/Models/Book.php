<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $guarded =  [];

    public function shelves(){
        return $this->belongsTo(Shelf::class, 'shelf_id');
    }
    public function authors(){
        return $this->belongsToMany(Author::class, 'books_authors');
    }

}
