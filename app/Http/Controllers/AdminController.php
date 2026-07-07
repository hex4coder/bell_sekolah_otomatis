<?php

namespace App\Http\Controllers;

use App\Models\AudioAsset;
use App\Models\BellSchedule;
use App\Models\SchoolDay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $audioDir = base_path('assets_audio');
        $audioFiles = [];
        $totalSize = 0;

        if (is_dir($audioDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($audioDir));
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['wav', 'mp3', 'ogg'])) {
                    $audioFiles[] = $file;
                    $totalSize += $file->getSize();
                }
            }
        }

        $audioCount = count($audioFiles);
        $audioSizeFormatted = $totalSize > 1073741824
            ? round($totalSize / 1073741824, 2) . ' GB'
            : ($totalSize > 1048576
                ? round($totalSize / 1048576, 2) . ' MB'
                : round($totalSize / 1024, 2) . ' KB');

        return view('admin.dashboard', [
            'totalSchedules' => BellSchedule::count(),
            'totalUsers' => User::count(),
            'totalAudioAssets' => AudioAsset::count(),
            'audioFileCount' => $audioCount,
            'audioSizeFormatted' => $audioSizeFormatted,
        ]);
    }

    public function audioIndex()
    {
        $assets = AudioAsset::all();
        $files = [];

        foreach ($assets as $asset) {
            $filePath = base_path('assets_audio/' . $asset->filename);
            if (!file_exists($filePath)) {
                continue;
            }

            $files[] = (object) [
                'id' => $asset->id,
                'name' => $asset->name,
                'filename' => $asset->filename,
                'path' => $asset->path,
                'size' => $asset->size,
                'size_formatted' => $asset->size > 1048576
                    ? round($asset->size / 1048576, 2) . ' MB'
                    : round($asset->size / 1024, 2) . ' KB',
            ];
        }

        usort($files, fn($a, $b) => strcmp($a->name, $b->name));

        return view('admin.audio.index', ['files' => $files]);
    }

    public function audioUpload(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:wav,mp3,ogg|max:51200',
            'name' => 'required|string|max:255',
        ]);

        $file = $request->file('audio_file');
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $ext;
        $mime = $file->getClientMimeType();
        $size = $file->getSize();

        $targetDir = base_path('assets_audio');

        try {
            $file->move($targetDir, $filename);
        } catch (\Exception $e) {
            return redirect()->route('admin.audio.index')->with('error', 'Gagal menyimpan file: ' . $e->getMessage());
        }

        AudioAsset::create([
            'name' => $request->name,
            'filename' => $filename,
            'path' => $filename,
            'mime_type' => $mime,
            'size' => $size,
        ]);

        return redirect()->route('admin.audio.index')->with('success', 'File audio "' . $request->name . '" berhasil diupload.');
    }

    public function audioDelete(Request $request, string $filename)
    {
        $safeName = basename($filename);
        $filePath = base_path('assets_audio/' . $safeName);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        AudioAsset::where('filename', $safeName)->delete();

        return redirect()->route('admin.audio.index')->with('success', 'File audio berhasil dihapus.');
    }

    public function audioEdit(Request $request, string $filename)
    {
        $safeName = basename($filename);
        $filePath = base_path('assets_audio/' . $safeName);

        if (! file_exists($filePath)) {
            return redirect()->route('admin.audio.index')->with('error', 'File tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        AudioAsset::updateOrCreate(
            ['filename' => $safeName],
            ['name' => $request->name, 'path' => $safeName]
        );

        return redirect()->route('admin.audio.index')->with('success', 'Nama audio berhasil diperbarui.');
    }

    public function schoolDays()
    {
        $days = SchoolDay::orderBy('day_of_week')->get();

        return view('admin.school-days', ['days' => $days]);
    }

    public function schoolDaysUpdate(Request $request)
    {
        $data = $request->validate([
            'days' => 'required|array',
            'days.*.id' => 'required|exists:school_days,id',
            'days.*.day_of_week' => 'required|integer|between:0,6',
            'days.*.is_active' => 'sometimes|boolean',
        ]);

        foreach ($data['days'] as $dayData) {
            SchoolDay::where('id', $dayData['id'])->update([
                'is_active' => isset($dayData['is_active']) && $dayData['is_active'] === '1',
            ]);
        }

        return redirect()->route('admin.school-days')->with('success', 'Pengaturan hari sekolah berhasil disimpan.');
    }

    public function schedules()
    {
        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $activeDays = SchoolDay::where('is_active', true)->pluck('day_of_week')->toArray();
        $days = [];

        foreach (range(0, 6) as $day) {
            if (!in_array($day, $activeDays)) {
                continue;
            }
            $days[] = (object) [
                'day_of_week' => $day,
                'name' => $dayNames[$day],
                'schedules' => BellSchedule::where('day_of_week', $day)
                    ->orderBy('time')
                    ->get(),
            ];
        }

        $audioFiles = AudioAsset::orderBy('name')->get();

        $days = collect($days)->values()->all();
        $audioNameMap = AudioAsset::pluck('name', 'filename');

        return view('admin.schedules.index', [
            'days' => $days,
            'audioFiles' => $audioFiles,
            'audioNameMap' => $audioNameMap,
            'activeDays' => $activeDays,
        ]);
    }

    public function schedulesStore(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required_without:all_days|integer|between:0,6',
            'name' => 'required|string|max:255',
            'time' => 'required|date_format:H:i',
            'audio_file' => 'nullable|string|max:255',
            'all_days' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        if (!empty($validated['all_days'])) {
            $activeDays = SchoolDay::where('is_active', true)->pluck('day_of_week')->toArray();
            foreach ($activeDays as $day) {
                BellSchedule::create([
                    'day_of_week' => $day,
                    'name' => $validated['name'],
                    'time' => $validated['time'],
                    'audio_file' => $validated['audio_file'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                ]);
            }
            $dayLabels = array_map(fn($d) => $dayNames[$d] ?? '?', $activeDays);
            $label = implode(', ', $dayLabels);
            return redirect()->route('admin.schedules')
                ->with('success', "Jadwal bell berhasil ditambahkan untuk: {$label}.");
        }

        BellSchedule::create($validated);

        return redirect()->route('admin.schedules')->with('success', "Jadwal bell berhasil ditambahkan untuk {$dayNames[$validated['day_of_week']]}.");
    }

    public function schedulesUpdate(Request $request, BellSchedule $schedule)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'name' => 'required|string|max:255',
            'time' => 'required|date_format:H:i',
            'audio_file' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $schedule->update($validated);

        return redirect()->route('admin.schedules')->with('success', 'Jadwal bell berhasil diperbarui.');
    }

    public function schedulesDestroy(BellSchedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.schedules')->with('success', 'Jadwal bell berhasil dihapus.');
    }

    public function schedulesReset()
    {
        $count = BellSchedule::count();
        BellSchedule::truncate();

        return redirect()->route('admin.schedules')
            ->with('success', "Semua jadwal bell ({$count} jadwal) berhasil direset.");
    }

    public function schedulesDestroyDay(int $day)
    {
        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $dayNames[$day] ?? 'Unknown';
        $count = BellSchedule::where('day_of_week', $day)->count();

        BellSchedule::where('day_of_week', $day)->delete();

        return redirect()->route('admin.schedules')
            ->with('success', "Semua jadwal {$dayName} ({$count} jadwal) berhasil dihapus.");
    }

    public function schedulesCopy(Request $request)
    {
        $validated = $request->validate([
            'source_day' => 'required|integer|between:0,6',
            'target_days' => 'required|array',
            'target_days.*' => 'integer|between:0,6|different:source_day',
        ]);

        $sourceDay = $validated['source_day'];
        $targetDays = array_unique($validated['target_days']);

        $schedules = BellSchedule::where('day_of_week', $sourceDay)
            ->orderBy('time')
            ->get(['name', 'time', 'audio_file', 'is_active']);

        if ($schedules->isEmpty()) {
            return redirect()->route('admin.schedules')
                ->with('error', 'Tidak ada jadwal pada hari sumber.');
        }

        $copied = 0;
        foreach ($targetDays as $targetDay) {
            BellSchedule::where('day_of_week', $targetDay)->delete();

            foreach ($schedules as $schedule) {
                BellSchedule::create([
                    'day_of_week' => $targetDay,
                    'name' => $schedule->name,
                    'time' => $schedule->time,
                    'audio_file' => $schedule->audio_file,
                    'is_active' => $schedule->is_active,
                ]);
                $copied++;
            }
        }

        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $sourceName = $dayNames[$sourceDay];

        return redirect()->route('admin.schedules')
            ->with('success', "Jadwal {$sourceName} berhasil disalin ke " . count($targetDays) . " hari ({$copied} jadwal).");
    }

    public function schedulesGenerateDefault()
    {
        BellSchedule::truncate();

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\BellScheduleSeeder', '--force' => true]);

        return redirect()->route('admin.schedules')
            ->with('success', 'Jadwal default berhasil dibuat.');
    }

    public function bellDarurat(Request $request)
    {
        $input = $request->input('audio_file');

        if ($input) {
            $asset = AudioAsset::where('filename', $input)->first();
            if (!$asset) {
                return response()->json(['error' => 'Aset audio tidak ditemukan.'], 404);
            }
            $audioFile = $asset->filename;
            $label = $asset->name;
        } else {
            $today = now()->dayOfWeek;
            $schedule = BellSchedule::where('day_of_week', $today)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', '%akhir%')
                      ->orWhere('name', 'Pulang');
                })
                ->orderBy('time', 'desc')
                ->first();

            if (!$schedule || !$schedule->audio_file) {
                $dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                return response()->json(['error' => 'Tidak ada jadwal bell pulang untuk hari ' . $dayNames[$today] . '.'], 404);
            }

            $audioFile = $schedule->audio_file;
            $asset = AudioAsset::where('filename', $audioFile)->first();
            $label = $asset ? $asset->name : $audioFile;
        }

        $audioPath = base_path('assets_audio/' . $audioFile);
        if (!file_exists($audioPath)) {
            return response()->json(['error' => 'File audio tidak ditemukan di penyimpanan: ' . $audioFile], 404);
        }

        Cache::put('emergency_bell', $audioFile, now()->addMinutes(2));

        return response()->json([
            'success' => true,
            'audio_file' => $audioFile,
            'label' => $label,
        ]);
    }
}
