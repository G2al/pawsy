<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'surname' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '1234567890',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'phone' => '12345678910',
            'email_verified_at' => now(),
        ]);
    }
}