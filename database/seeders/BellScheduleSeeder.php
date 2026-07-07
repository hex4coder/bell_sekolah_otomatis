<?php

namespace Database\Seeders;

use App\Models\BellSchedule;
use Illuminate\Database\Seeder;

class BellScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $monday = [
            ['name' => '5 Menit Awal Upacara', 'time' => '06:25', 'audio_file' => '5_menit_awal_upacara.wav'],
            ['name' => 'Upacara Bendera', 'time' => '06:30', 'audio_file' => 'mars_smk.wav'],
            ['name' => 'Jam Ke-1', 'time' => '07:00', 'audio_file' => 'jam_ke_1.wav'],
            ['name' => 'Jam Ke-2', 'time' => '07:45', 'audio_file' => 'jam_ke_2.wav'],
            ['name' => '5 Menit Akhir Istirahat', 'time' => '08:25', 'audio_file' => '5_menit_akhir_istirahat.wav'],
            ['name' => 'Jam Ke-3', 'time' => '08:30', 'audio_file' => 'jam_ke_3.wav'],
            ['name' => 'Jam Ke-4', 'time' => '09:15', 'audio_file' => 'jam_ke_4.wav'],
            ['name' => 'Istirahat', 'time' => '10:00', 'audio_file' => 'istirahat.wav'],
            ['name' => '5 Menit Awal Jam Ke-5', 'time' => '10:15', 'audio_file' => '5_menit_awal_jam_ke_1.wav'],
            ['name' => 'Jam Ke-5', 'time' => '10:20', 'audio_file' => 'jam_ke_5.wav'],
            ['name' => 'Jam Ke-6', 'time' => '11:05', 'audio_file' => 'jam_ke_6.wav'],
            ['name' => 'Jam Ke-7', 'time' => '11:50', 'audio_file' => 'jam_ke_7.wav'],
            ['name' => 'Istirahat', 'time' => '12:35', 'audio_file' => 'istirahat_1.wav'],
            ['name' => 'Jam Ke-8', 'time' => '12:50', 'audio_file' => 'jam_ke_8.wav'],
            ['name' => 'Jam Ke-9', 'time' => '13:35', 'audio_file' => 'jam_ke_9.wav'],
            ['name' => 'Akhir Pelajaran', 'time' => '14:20', 'audio_file' => 'akhir_pelajaran_1.wav'],
        ];

        $weekday = [
            ['name' => '5 Menit Awal Jam Ke-1', 'time' => '06:55', 'audio_file' => '5_menit_awal_jam_ke_1.wav'],
            ['name' => 'Jam Ke-1', 'time' => '07:00', 'audio_file' => 'jam_ke_1.wav'],
            ['name' => 'Jam Ke-2', 'time' => '07:45', 'audio_file' => 'jam_ke_2.wav'],
            ['name' => '5 Menit Akhir Istirahat', 'time' => '08:25', 'audio_file' => '5_menit_akhir_istirahat.wav'],
            ['name' => 'Jam Ke-3', 'time' => '08:30', 'audio_file' => 'jam_ke_3.wav'],
            ['name' => 'Jam Ke-4', 'time' => '09:15', 'audio_file' => 'jam_ke_4.wav'],
            ['name' => 'Ayo Senam', 'time' => '09:55', 'audio_file' => 'ayo_senam.wav'],
            ['name' => 'Istirahat', 'time' => '10:10', 'audio_file' => 'istirahat.wav'],
            ['name' => '5 Menit Awal Jam Ke-5', 'time' => '10:20', 'audio_file' => '5_menit_awal_jam_ke_1.wav'],
            ['name' => 'Jam Ke-5', 'time' => '10:25', 'audio_file' => 'jam_ke_5.wav'],
            ['name' => 'Jam Ke-6', 'time' => '11:10', 'audio_file' => 'jam_ke_6.wav'],
            ['name' => 'Jam Ke-7', 'time' => '11:55', 'audio_file' => 'jam_ke_7.wav'],
            ['name' => 'Jam Ke-8', 'time' => '12:40', 'audio_file' => 'jam_ke_8.wav'],
            ['name' => 'Jam Ke-9', 'time' => '13:25', 'audio_file' => 'jam_ke_9.wav'],
            ['name' => 'Jam Ke-10', 'time' => '14:10', 'audio_file' => 'jam_ke_10.wav'],
            ['name' => 'Akhir Pelajaran', 'time' => '14:55', 'audio_file' => 'akhir_pelajaran_2.wav'],
        ];

        $friday = [
            ['name' => '5 Menit Awal Keg. Keagamaan', 'time' => '06:25', 'audio_file' => '5_menit_awal_kegiatan_keagamaan.wav'],
            ['name' => 'Kegiatan Keagamaan', 'time' => '06:30', 'audio_file' => 'sholawat_jibril.wav'],
            ['name' => 'Jam Ke-1', 'time' => '07:00', 'audio_file' => 'jam_ke_1.wav'],
            ['name' => 'Jam Ke-2', 'time' => '07:40', 'audio_file' => 'jam_ke_2.wav'],
            ['name' => '5 Menit Akhir Istirahat', 'time' => '08:15', 'audio_file' => '5_menit_akhir_istirahat_1.wav'],
            ['name' => 'Jam Ke-3', 'time' => '08:20', 'audio_file' => 'jam_ke_3.wav'],
            ['name' => 'Jam Ke-4', 'time' => '09:00', 'audio_file' => 'jam_ke_4.wav'],
            ['name' => 'Istirahat', 'time' => '09:40', 'audio_file' => 'istirahat_2.wav'],
            ['name' => 'Jam Ke-5', 'time' => '10:00', 'audio_file' => 'jam_ke_5.wav'],
            ['name' => 'Jam Ke-6', 'time' => '10:40', 'audio_file' => 'jam_ke_6.wav'],
            ['name' => 'Jam Ke-7', 'time' => '11:20', 'audio_file' => 'jam_ke_7.wav'],
            ['name' => 'Akhir Pelajaran', 'time' => '12:00', 'audio_file' => 'akhir_pelajaran_1.wav'],
        ];

        $saturday = [
            ['name' => '5 Menit Awal Jam Ke-1', 'time' => '06:55', 'audio_file' => '5_menit_awal_jam_ke_1.wav'],
            ['name' => 'Jam Ke-1', 'time' => '07:00', 'audio_file' => 'jam_ke_1.wav'],
            ['name' => 'Jam Ke-2', 'time' => '07:40', 'audio_file' => 'jam_ke_2.wav'],
            ['name' => 'Jam Ke-3', 'time' => '08:20', 'audio_file' => 'jam_ke_3.wav'],
            ['name' => 'Jam Ke-4', 'time' => '09:00', 'audio_file' => 'jam_ke_4.wav'],
            ['name' => 'Ayo Senam', 'time' => '09:40', 'audio_file' => 'ayo_senam.wav'],
            ['name' => 'Akhir Pelajaran', 'time' => '10:00', 'audio_file' => 'akhir_pelajaran_2.wav'],
        ];

        $days = [
            1 => $monday,
            2 => $weekday,
            3 => $weekday,
            4 => $weekday,
            5 => $friday,
            6 => $saturday,
        ];

        foreach ($days as $dayOfWeek => $schedules) {
            foreach ($schedules as $schedule) {
                BellSchedule::create(array_merge($schedule, ['day_of_week' => $dayOfWeek]));
            }
        }
    }
}
