<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'monthly_rental_id', // ✅ thêm
        'amount',
        'method',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // Thuê giờ
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Thuê tháng
    public function monthlyRental()
    {
        return $this->belongsTo(MonthlyRental::class);
    }
}

