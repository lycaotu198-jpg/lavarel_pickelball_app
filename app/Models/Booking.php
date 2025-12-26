<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'court_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
    ];

    /* =====================
       Relationships
    ===================== */

    // Booking thuộc về User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Booking thuộc về Court
    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    // Booking có 1 Payment
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function monthlyRental()
    {
        return $this->belongsTo(MonthlyRental::class);
    }

}
