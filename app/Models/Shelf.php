<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bookshelf_id',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function bookshelf()
    {
        return $this->belongsTo(Bookshelf::class, 'bookshelf_id');
    }

    public static function getShelves(int $bookshelfId)
    {
        return self::query()
            ->where('bookshelf_id', $bookshelfId)
            ->get();
    }
}