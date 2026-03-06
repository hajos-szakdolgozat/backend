<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatImage extends Model
{
    /** @use HasFactory<\Database\Factories\BoatImageFactory> */
    use HasFactory;
    protected $fillable = [
        'boat_id',
        'path',
        'is_thumbnail'
    ];

    protected $appends = [
        'image_url',
    ];

    //megszorítás
    public static $rules = [
        'path' => 'required|string',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function boat()
    {
        return $this->belongsTo(Boat::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return asset('storage/'.$this->path);
    }
}
