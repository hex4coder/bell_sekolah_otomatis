<?php

namespace Database\Seeders;

use App\Models\AudioAsset;
use App\Models\BellPlaylist;
use Illuminate\Database\Seeder;

class BellPlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $songAssets = AudioAsset::whereIn('filename', [
            'mars_smk.wav',
            '1783411183_audiobelljam10.wav',
            'pelajar_pancasila.wav',
        ])->get();

        $assets = $songAssets->map(fn ($a) => [
            'asset_id' => $a->id,
            'filename' => $a->filename,
            'name' => $a->name,
        ])->values()->toArray();

        BellPlaylist::truncate();

        BellPlaylist::create([
            'type' => 'opening',
            'name' => 'Pembuka Senin',
            'audio_assets' => $assets,
            'time_range_start' => '06:00',
            'time_range_end' => '07:24',
            'is_active' => true,
            'day_of_week' => [1],
            'order' => 0,
        ]);

        BellPlaylist::create([
            'type' => 'opening',
            'name' => 'Pembuka Selasa-Jumat',
            'audio_assets' => $assets,
            'time_range_start' => '06:00',
            'time_range_end' => '07:29',
            'is_active' => true,
            'day_of_week' => [2, 3, 4, 5],
            'order' => 0,
        ]);

        BellPlaylist::create([
            'type' => 'closing',
            'name' => 'Penutup Senin-Kamis',
            'audio_assets' => $assets,
            'time_range_start' => '15:36',
            'time_range_end' => '16:05',
            'is_active' => true,
            'day_of_week' => [1, 2, 3, 4],
            'order' => 0,
        ]);

        BellPlaylist::create([
            'type' => 'closing',
            'name' => 'Penutup Jumat',
            'audio_assets' => $assets,
            'time_range_start' => '11:46',
            'time_range_end' => '12:15',
            'is_active' => true,
            'day_of_week' => [5],
            'order' => 0,
        ]);
    }
}
