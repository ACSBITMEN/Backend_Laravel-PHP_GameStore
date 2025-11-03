<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@videogames.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567890',
            'country' => 'Colombia',
            'role_id' => 2, // admin
            'status' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'first_name' => 'Manager',
            'last_name' => 'User', 
            'email' => 'manager@videogames.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567891',
            'country' => 'Colombia',
            'role_id' => 1, // manager
            'status' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'first_name' => 'Customer',
            'last_name' => 'User',
            'email' => 'customer@videogames.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567892',
            'country' => 'Colombia', 
            'role_id' => 3, // customer
            'status' => true,
            'email_verified_at' => Carbon::now(),
        ]);
    }
}