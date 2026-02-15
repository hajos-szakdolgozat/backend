<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{
    /** @use HasFactory<\Database\Factories\BoatFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'port_id',
        'name',
        'description',
        'price_per_night',
        'is_active',
        'type',
        'year_built',
        'width',
        'length',
        'draft'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_night' => 'integer',
        'year_built' => 'integer',
        'width' => 'float',
        'length' => 'float',
        'draft' => 'float',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    public function boatImages()
    {
        return $this->hasMany(BoatImage::class);
    }
}
