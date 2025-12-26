<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Court;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tạo danh sách sân pickleball
        |--------------------------------------------------------------------------
        */
        Court::create([
            'name' => 'Court A',
            'location' => 'Khu A - Sân trung tâm',
            'address' => '123 Đường ABC, Quận 1, TP.HCM',
            'price_per_hour' => 150000,
            'status' => 'available',
            'latitude' => 10.7769,
            'longitude' => 106.7009,
        ]);

        Court::create([
            'name' => 'Court B',
            'location' => 'Khu B - Sân ngoài trời',
            'address' => '456 Đường XYZ, Quận 2, TP.HCM',
            'price_per_hour' => 120000,
            'status' => 'available',
            'latitude' => 10.8019,
            'longitude' => 106.7392,
        ]);

        Court::create([
            'name' => 'Court C',
            'location' => 'Khu C - Sân có mái che',
            'address' => '789 Đường DEF, Quận 3, TP.HCM',
            'price_per_hour' => 180000,
            'status' => 'maintenance',
            'latitude' => 10.7841,
            'longitude' => 106.6932,
        ]);
    }
}
