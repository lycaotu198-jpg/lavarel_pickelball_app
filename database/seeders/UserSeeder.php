<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Tạo ADMIN (dữ liệu cố định)
        |--------------------------------------------------------------------------
        */
        if (!User::where('email', 'admin@pickleball.com')->exists()) {
            User::create([
                'name' => 'Admin Pickleball',
                'email' => 'admin@pickleball.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '0123456789',
                'address' => '123 Đường Quản Trị, Quận 1, TP.HCM',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Tạo KHÁCH THUÊ SÂN (dùng Factory)
        |--------------------------------------------------------------------------
        */
        User::factory()->count(10)->create();
    }
}
