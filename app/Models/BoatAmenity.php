<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatAmenity extends Model
{
    /** @use HasFactory<\Database\Factories\BoatAmenityFactory> */
    use HasFactory;
    protected $fillable = [
        'boat_id',
        'amenity_id'
    ];
    public function boat()
    {
        return $this->belongsTo(Boat::class);
    }

    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
