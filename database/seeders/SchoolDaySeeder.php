<?php

namespace Database\Seeders;

use App\Models\SchoolDay;
use Illuminate\Database\Seeder;

class SchoolDaySeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            ['day_of_week' => 0, 'name' => 'Minggu',  'is_active' => false],
            ['day_of_week' => 1, 'name' => 'Senin',   'is_active' => true],
            ['day_of_week' => 2, 'name' => 'Selasa',  'is_active' => true],
            ['day_of_week' => 3, 'name' => 'Rabu',    'is_active' => true],
            ['day_of_week' => 4, 'name' => 'Kamis',   'is_active' => true],
            ['day_of_week' => 5, 'name' => 'Jumat',   'is_active' => true],
            ['day_of_week' => 6, 'name' => 'Sabtu',   'is_active' => false],
        ];

        foreach ($days as $day) {
            SchoolDay::create($day);
        }

        $this->command->info('School days seeded: Mon-Fri active, Sat-Sun inactive.');
    }
}
