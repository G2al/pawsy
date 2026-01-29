<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;

class BusinessHourSeeder extends Seeder
{
    public function run(): void
    {
        $hours = [
            ['day_of_week' => 0, 'is_closed' => true, 'morning_open' => null, 'morning_close' => null, 'afternoon_open' => null, 'afternoon_close' => null], // Domenica
            ['day_of_week' => 1, 'is_closed' => false, 'morning_open' => '09:00', 'morning_close' => '13:00', 'afternoon_open' => '15:00', 'afternoon_close' => '20:00'], // Lunedì
            ['day_of_week' => 2, 'is_closed' => false, 'morning_open' => '09:00', 'morning_close' => '13:00', 'afternoon_open' => '15:00', 'afternoon_close' => '20:00'], // Martedì
            ['day_of_week' => 3, 'is_closed' => false, 'morning_open' => '09:00', 'morning_close' => '13:00', 'afternoon_open' => '15:00', 'afternoon_close' => '20:00'], // Mercoledì
            ['day_of_week' => 4, 'is_closed' => false, 'morning_open' => '09:00', 'morning_close' => '13:00', 'afternoon_open' => '15:00', 'afternoon_close' => '20:00'], // Giovedì
            ['day_of_week' => 5, 'is_closed' => false, 'morning_open' => '09:00', 'morning_close' => '13:00', 'afternoon_open' => '15:00', 'afternoon_close' => '20:00'], // Venerdì
            ['day_of_week' => 6, 'is_closed' => false, 'morning_open' => '09:00', 'morning_close' => '13:00', 'afternoon_open' => '15:00', 'afternoon_close' => '20:00'], // Sabato
        ];

        foreach ($hours as $hour) {
            BusinessHour::create($hour);
        }
    }
}