<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'ISBN',
        'title',
        'publisher',
        'pages',
        'img',
        'shelf_id',
    ];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class, 'shelf_id');
    }

    public function authors()
    {
        return $this->belongsToMany(
            Author::class,
            'books_authors',
            'book_id',
            'author_id'
        );
    }
}