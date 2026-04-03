<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'books_authors');
    }

    public static function findAuthor(string $name, string $surname): ?Author
    {
        $name = trim($name);
        $surname = trim($surname);

        return self::query()
            ->where('name', $name)
            ->where('surname', $surname)
            ->first();
    }
}