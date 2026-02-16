<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    /** @use HasFactory<\Database\Factories\AmenityFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description'
    ];

    public function boats()
    {
        return $this->belongsToMany(Boat::class, 'boat_amenities');
    }
}
