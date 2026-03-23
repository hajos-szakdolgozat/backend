<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'boat_id',
        'status',
        'start_date',
        'end_date',

    ];

    //Reservations időintervallumának átfedésének tiltása
    public static function checkOverlap($boatId, $startDate, $endDate)
    {
        return self::where('boat_id', $boatId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })->exists();
    }

    public function review()
    {
        return $this->hasOne(Review::class);
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
