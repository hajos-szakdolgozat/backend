<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatImage extends Model
{
    /** @use HasFactory<\Database\Factories\BoatImageFactory> */
    use HasFactory;
    protected $fillable = [
        'path',
        'is_thumbnail'
    ];
}
