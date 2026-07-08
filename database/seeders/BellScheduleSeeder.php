<?php

namespace Database\Seeders;

use App\Models\BellSchedule;
use Illuminate\Database\Seeder;

class BellScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $audioMap = [
            '5 Menit Awal Jam ke-1' => '5_menit_awal_jam_ke_1.wav',
            '5 Menit Awal Upacara' => '5_menit_awal_upacara.wav',
            '5 Menit Awal Kegiatan Keagamaan' => '5_menit_awal_kegiatan_keagamaan.wav',
            'Jam ke-1' => 'jam_ke_1.wav',
            'Jam ke-2' => 'jam_ke_2.wav',
            'Jam ke-3' => 'jam_ke_3.wav',
            'Jam ke-4' => 'jam_ke_4.wav',
            'Jam ke-5' => 'jam_ke_5.wav',
            'Jam ke-6' => 'jam_ke_6.wav',
            'Jam ke-7' => 'jam_ke_7.wav',
            'Jam ke-8' => 'jam_ke_8.wav',
            'Jam ke-9' => 'jam_ke_9.wav',
            'Jam ke-10' => 'jam_ke_10.wav',
            'Jam ke-11' => 'jam_ke_11.wav',
            'Jam ke-12' => 'jam_ke_12.wav',
            'Jam ke-13' => 'jam_ke_13.wav',
            'Jam ke-14' => 'jam_ke_14.wav',
            'Istirahat 1' => 'istirahat_1.wav',
            'Istirahat 2' => 'istirahat_2.wav',
            'Istirahat' => 'istirahat.wav',
            'Akhir Istirahat 1' => '5_menit_akhir_istirahat_1.wav',
            'Akhir Istirahat 2' => '5_menit_akhir_istirahat_2.wav',
            'Akhir Istirahat' => '5_menit_akhir_istirahat.wav',
            'Pulang' => 'akhir_pelajaran_1.wav',
            'Akhir Pelajaran 2' => 'akhir_pelajaran_2.wav',
            'Akhir Pekan 1' => 'akhir_pekan_1.wav',
            'Akhir Pekan 2' => 'akhir_pekan_2.wav',
            'Ayo Senam' => 'ayo_senam.wav',
            'Mars SMK' => 'mars_smk.wav',
            'Pelajar Pancasila' => 'pelajar_pancasila.wav',
            'Sholawat Jibril' => 'sholawat_jibril.wav',
            'Lagu Indonesia Raya' => '1783411183_audiobelljam10.wav',
        ];

        $monday = [
            ['time' => '07:25', 'name' => '5 Menit Awal Upacara'],
            ['time' => '08:05', 'name' => '5 Menit Awal Jam ke-1'],
            ['time' => '08:10', 'name' => 'Jam ke-1'],
            ['time' => '08:50', 'name' => 'Jam ke-2'],
            ['time' => '09:30', 'name' => 'Istirahat 1'],
            ['time' => '09:40', 'name' => 'Akhir Istirahat 1'],
            ['time' => '09:45', 'name' => 'Jam ke-3'],
            ['time' => '10:25', 'name' => 'Jam ke-4'],
            ['time' => '11:05', 'name' => 'Jam ke-5'],
            ['time' => '11:45', 'name' => 'Istirahat 2'],
            ['time' => '12:10', 'name' => 'Akhir Istirahat 2'],
            ['time' => '12:15', 'name' => 'Jam ke-6'],
            ['time' => '12:55', 'name' => 'Jam ke-7'],
            ['time' => '13:35', 'name' => 'Jam ke-8'],
            ['time' => '14:15', 'name' => 'Jam ke-9'],
            ['time' => '14:55', 'name' => 'Jam ke-10'],
            ['time' => '15:35', 'name' => 'Pulang'],
        ];

        $tueToThu = [
            ['time' => '07:25', 'name' => '5 Menit Awal Jam ke-1'],
            ['time' => '07:30', 'name' => 'Jam ke-1'],
            ['time' => '08:10', 'name' => 'Jam ke-2'],
            ['time' => '08:50', 'name' => 'Jam ke-3'],
            ['time' => '09:30', 'name' => 'Istirahat 1'],
            ['time' => '09:40', 'name' => 'Akhir Istirahat 1'],
            ['time' => '09:45', 'name' => 'Jam ke-4'],
            ['time' => '10:25', 'name' => 'Jam ke-5'],
            ['time' => '11:05', 'name' => 'Jam ke-6'],
            ['time' => '11:45', 'name' => 'Istirahat 2'],
            ['time' => '12:10', 'name' => 'Akhir Istirahat 2'],
            ['time' => '12:15', 'name' => 'Jam ke-7'],
            ['time' => '12:55', 'name' => 'Jam ke-8'],
            ['time' => '13:35', 'name' => 'Jam ke-9'],
            ['time' => '14:15', 'name' => 'Jam ke-10'],
            ['time' => '14:55', 'name' => 'Jam ke-11'],
            ['time' => '15:35', 'name' => 'Pulang'],
        ];

        $friday = [
            ['time' => '07:25', 'name' => '5 Menit Awal Jam ke-1'],
            ['time' => '07:30', 'name' => 'Jam ke-1'],
            ['time' => '08:10', 'name' => 'Jam ke-2'],
            ['time' => '08:50', 'name' => 'Jam ke-3'],
            ['time' => '09:30', 'name' => 'Istirahat'],
            ['time' => '09:40', 'name' => 'Akhir Istirahat'],
            ['time' => '09:45', 'name' => 'Jam ke-4'],
            ['time' => '10:25', 'name' => 'Jam ke-5'],
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
                $audioFile = $audioMap[$schedule['name']] ?? null;
                
                // Special condition for Friday dismissal bell
                if ($dayOfWeek == 5 && $schedule['name'] == 'Pulang') {
                    $audioFile = 'akhir_pekan_1.wav';
                }
                
                BellSchedule::create([
                    'day_of_week' => $dayOfWeek,
                    'name' => $schedule['name'],
                    'time' => $schedule['time'],
                    'audio_file' => $audioFile,
                    'is_active' => true,
                ]);
            }
        }
    }
}
