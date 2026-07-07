<?php

namespace Database\Seeders;

use App\Models\AudioAsset;
use Illuminate\Database\Seeder;

class AudioAssetSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
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
            'istirahat.wav' => 'Istirahat',
            'akhir_pelajaran_1.wav' => 'Pulang',
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
