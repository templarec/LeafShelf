<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookshelf extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'room_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public static function getBookshelves(int $roomId)
    {
        return self::query()
            ->where('room_id', $roomId)
            ->get();
    }
}