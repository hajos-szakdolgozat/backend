<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;
    protected $fillable = [
        'reservation_id',
        'rating',
        'comment',
    ];


    protected $casts = [
        'rating' => 'integer',
    ];  

    //megszorítás
    public static $rules = [
        'rating' => 'required|integer|min:1|max:5',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
