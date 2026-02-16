<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;
    protected $fillable = [
        'reservation_id',
        'amount',
        'status',
        'payment_method'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
