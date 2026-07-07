<?php

namespace Database\Seeders;

use App\Models\BellSchedule;
use Illuminate\Database\Seeder;

class BellScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $audioMap = [
            'Upacara Bendera' => 'bel_upacara.wav',
            'Jam ke-1' => 'bel_jam_1.wav',
            'Jam ke-2' => 'bel_jam_2.wav',
            'Jam ke-3' => 'bel_jam_3.wav',
            'Jam ke-4' => 'bel_jam_4.wav',
            'Jam ke-5' => 'bel_jam_5.wav',
            'Jam ke-6' => 'bel_jam_6.wav',
            'Jam ke-7' => 'bel_jam_7.wav',
            'Jam ke-8' => 'bel_jam_8.wav',
            'Jam ke-9' => 'bel_jam_9.wav',
            'Jam ke-10' => 'bel_jam_10.wav',
            'Istirahat' => 'bel_istirahat.wav',
            'Pulang' => 'bel_pulang.wav',
        ];

        $monday = [
            ['time' => '07:30', 'name' => 'Upacara Bendera'],
            ['time' => '08:10', 'name' => 'Jam ke-1'],
            ['time' => '08:50', 'name' => 'Jam ke-2'],
            ['time' => '09:30', 'name' => 'Jam ke-3'],
            ['time' => '10:10', 'name' => 'Istirahat'],
            ['time' => '10:50', 'name' => 'Jam ke-4'],
            ['time' => '11:30', 'name' => 'Jam ke-5'],
            ['time' => '12:10', 'name' => 'Istirahat'],
            ['time' => '13:00', 'name' => 'Jam ke-6'],
            ['time' => '13:40', 'name' => 'Jam ke-7'],
            ['time' => '14:20', 'name' => 'Jam ke-8'],
            ['time' => '15:00', 'name' => 'Pulang'],
        ];

        $tueToThu = [
            ['time' => '07:30', 'name' => 'Jam ke-1'],
            ['time' => '08:10', 'name' => 'Jam ke-2'],
            ['time' => '08:50', 'name' => 'Jam ke-3'],
            ['time' => '09:30', 'name' => 'Jam ke-4'],
            ['time' => '10:10', 'name' => 'Istirahat'],
            ['time' => '10:50', 'name' => 'Jam ke-5'],
            ['time' => '11:30', 'name' => 'Jam ke-6'],
            ['time' => '12:10', 'name' => 'Istirahat'],
            ['time' => '13:00', 'name' => 'Jam ke-7'],
            ['time' => '13:40', 'name' => 'Jam ke-8'],
            ['time' => '14:20', 'name' => 'Jam ke-9'],
            ['time' => '15:00', 'name' => 'Jam ke-10'],
            ['time' => '15:40', 'name' => 'Pulang'],
        ];

        $friday = [
            ['time' => '07:30', 'name' => 'Jam ke-1'],
            ['time' => '08:10', 'name' => 'Jam ke-2'],
            ['time' => '08:50', 'name' => 'Jam ke-3'],
            ['time' => '09:30', 'name' => 'Jam ke-4'],
            ['time' => '10:10', 'name' => 'Istirahat'],
            ['time' => '10:50', 'name' => 'Jam ke-5'],
            ['time' => '11:45', 'name' => 'Pulang'],
        ];

        $days = [
            1 => $monday,
            2 => $tueToThu,
            3 => $tueToThu,
            4 => $tueToThu,
            5 => $friday,
        ];

        foreach ($days as $dayOfWeek => $schedules) {
            foreach ($schedules as $schedule) {
                BellSchedule::create([
                    'day_of_week' => $dayOfWeek,
                    'name' => $schedule['name'],
                    'time' => $schedule['time'],
                    'audio_file' => $audioMap[$schedule['name']] ?? null,
                    'is_active' => true,
                ]);
            }
        }
    }
}
