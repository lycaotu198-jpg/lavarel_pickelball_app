<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyRental extends Model
{
    protected $fillable = [
        'user_id',
        'court_id',
        'start_date',
        'end_date',
        'week_days',
        'start_time',
        'end_time',
        'monthly_price',
        'status',
    ];

    protected $casts = [
        'week_days' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
