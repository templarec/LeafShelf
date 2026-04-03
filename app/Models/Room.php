<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'building_id',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function bookshelves()
    {
        return $this->hasMany(Bookshelf::class);
    }

    public static function getRooms(int $buildingId)
    {
        return self::query()
            ->where('building_id', $buildingId)
            ->get();
    }
}