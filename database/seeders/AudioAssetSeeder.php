<?php

namespace Database\Seeders;

use App\Models\AudioAsset;
use Illuminate\Database\Seeder;

class AudioAssetSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            'bel_upacara.wav' => 'Bel Upacara',
            'bel_jam_1.wav' => 'Bel Jam ke-1',
            'bel_jam_2.wav' => 'Bel Jam ke-2',
            'bel_jam_3.wav' => 'Bel Jam ke-3',
            'bel_jam_4.wav' => 'Bel Jam ke-4',
            'bel_jam_5.wav' => 'Bel Jam ke-5',
            'bel_jam_6.wav' => 'Bel Jam ke-6',
            'bel_jam_7.wav' => 'Bel Jam ke-7',
            'bel_jam_8.wav' => 'Bel Jam ke-8',
            'bel_jam_9.wav' => 'Bel Jam ke-9',
            'bel_jam_10.wav' => 'Bel Jam ke-10',
            'bel_istirahat.wav' => 'Bel Istirahat',
            'bel_pulang.wav' => 'Bel Pulang',
        ];

        foreach ($files as $filename => $name) {
            $path = base_path('assets_audio/' . $filename);
            if (!file_exists($path)) {
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

        $this->command->info('Audio assets seeded: ' . count($files) . ' files.');
    }
}
