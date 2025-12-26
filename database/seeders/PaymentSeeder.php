<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Booking;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = Booking::where('status', 'confirmed')->get();

        /*
        |--------------------------------------------------------------------------
        | Tạo thanh toán cho booking đã xác nhận
        |--------------------------------------------------------------------------
        */
        foreach ($bookings as $booking) {
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'method' => 'cash',
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }
}
