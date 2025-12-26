<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Court;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $users  = User::where('role', 'customer')->get();
        $courts = Court::where('status', 'available')->get();

        /*
        |--------------------------------------------------------------------------
        | Tạo booking mẫu
        |--------------------------------------------------------------------------
        */
        foreach ($users as $user) {
            Booking::create([
                'user_id' => $user->id,
                'court_id' => $courts->random()->id,
                'booking_date' => now()->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '09:00:00',
                'total_price' => 150000,
                'status' => 'confirmed',
            ]);
            Booking::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        }

    }
}
