<?php

namespace Database\Seeders;

use App\Models\AudioAsset;
use Illuminate\Database\Seeder;

class AudioAssetSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            '5_menit_akhir_istirahat.wav' => '5 Menit Akhir Istirahat',
            '5_menit_akhir_istirahat_1.wav' => '5 Menit Akhir Istirahat 1',
            '5_menit_akhir_istirahat_2.wav' => '5 Menit Akhir Istirahat 2',
            '5_menit_awal_jam_ke_1.wav' => '5 Menit Awal Jam ke-1',
            '5_menit_awal_kegiatan_keagamaan.wav' => '5 Menit Awal Kegiatan Keagamaan',
            '5_menit_awal_upacara.wav' => '5 Menit Awal Upacara',
            'akhir_pekan_1.wav' => 'Akhir Pekan 1',
            'akhir_pekan_2.wav' => 'Akhir Pekan 2',
            'akhir_pelajaran_1.wav' => 'Pulang',
            'akhir_pelajaran_2.wav' => 'Akhir Pelajaran 2',
            'ayo_senam.wav' => 'Ayo Senam',
            'ayo_senam.mp3' => 'Ayo Senam MP3',
            'istirahat.wav' => 'Istirahat',
            'istirahat_1.wav' => 'Istirahat 1',
            'istirahat_2.wav' => 'Istirahat 2',
            'jam_ke_1.wav' => 'Jam ke-1',
            'jam_ke_2.wav' => 'Jam ke-2',
            'jam_ke_3.wav' => 'Jam ke-3',
            'jam_ke_4.wav' => 'Jam ke-4',
            'jam_ke_5.wav' => 'Jam ke-5',
            'jam_ke_6.wav' => 'Jam ke-6',
            'jam_ke_7.wav' => 'Jam ke-7',
            'jam_ke_8.wav' => 'Jam ke-8',
            'jam_ke_9.wav' => 'Jam ke-9',
            'jam_ke_10.wav' => 'Jam ke-10',
            'jam_ke_11.wav' => 'Jam ke-11',
            'jam_ke_12.wav' => 'Jam ke-12',
            'jam_ke_13.wav' => 'Jam ke-13',
            'jam_ke_14.wav' => 'Jam ke-14',
            'mars_smk.wav' => 'Mars SMK',
            'pelajar_pancasila.wav' => 'Pelajar Pancasila',
            'sholawat_jibril.wav' => 'Sholawat Jibril',
            '1783411183_audiobelljam10.wav' => 'Lagu Indonesia Raya',
        ];

        foreach ($files as $filename => $name) {
            $path = base_path('assets_audio/' . $filename);
            if (!file_exists($path)) {
                $this->command->warn("File not found: {$filename}");
                continue;
            }

            AudioAsset::updateOrCreate(
                ['filename' => $filename],
                [
                    'name' => $name,
                    'path' => $filename,
                    'mime_type' => 'audio/wav',
                    'size' => filesize($path),
                ]
            );
        }
    }
}
