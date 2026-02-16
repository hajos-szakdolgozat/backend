<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    /** @use HasFactory<\Database\Factories\FavoriteFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'boat_id'
    ];

    public static $rules = [
        'user_id' => 'required|integer',
        'boat_id' => 'required|integer',
    ];

    //Egy felhasználó egy hajót csak egyszer kedvencelhet

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (Favorite::where('user_id', $model->user_id)->where('boat_id', $model->boat_id)->exists()) {
                throw new \Exception('This boat is already favorited by the user.');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boat()
    {
        return $this->belongsTo(Boat::class);
    }
}
