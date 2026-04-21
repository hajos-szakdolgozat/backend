<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    /** @use HasFactory<\Database\Factories\AmenityFactory> */
    use HasFactory;
    protected $fillable = [
        'slug',
        'name',
        'description'
    ];
    public function boatAmenities()
    {
        return $this->hasMany(BoatAmenity::class);
    }
}
