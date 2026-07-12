<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Models\BellPlaylist;
use App\Models\BellSchedule;
use App\Models\SchoolDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $today = Carbon::today();
    $dayOfWeek = $today->dayOfWeek;

    $schoolDay = SchoolDay::where('day_of_week', $dayOfWeek)->first();
    $isSchoolDay = $schoolDay?->is_active ?? false;

    $schedules = $isSchoolDay
        ? BellSchedule::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('time')
            ->get()
        : collect();

    $nowTime = now()->format('H:i');
    $firstBell = $schedules->first()?->time?->format('H:i');
    $lastBell = $schedules->last()?->time?->format('H:i');

    $playlists = BellPlaylist::where('is_active', true)
        ->orderBy('type')
        ->orderBy('order')
        ->get()
        ->filter(function ($p) use ($dayOfWeek) {
            $days = $p->day_of_week ?? [];
            return empty($days) || in_array($dayOfWeek, $days);
        });

    if (!$isSchoolDay || $schedules->isEmpty()) {
        $schoolStatus = 'Libur';
    } elseif ($firstBell && $nowTime < $firstBell) {
        $schoolStatus = 'Belum masuk';
    } elseif ($lastBell && $nowTime > $lastBell) {
        $schoolStatus = 'Selesai';
    } else {
        $schoolStatus = 'Berlangsung';
    }

    $activePlaylist = null;
    foreach ($playlists as $p) {
        if (!$isSchoolDay || $schedules->isEmpty()) break;

        $firstBell = $schedules->first()->time->format('H:i');
        $lastBell = $schedules->last()->time->format('H:i');

        $start = $p->type === 'opening'
            ? ($p->time_range_start?->format('H:i') ?? Carbon::createFromFormat('H:i', $firstBell)->subMinutes(15)->format('H:i'))
            : ($p->time_range_start?->format('H:i') ?? $lastBell);
        $end = $p->type === 'opening'
            ? ($p->time_range_end?->format('H:i') ?? $firstBell)
            : ($p->time_range_end?->format('H:i') ?? Carbon::createFromFormat('H:i', $lastBell)->addMinutes(15)->format('H:i'));

        if ($nowTime >= $start && $nowTime < $end) {
            $activePlaylist = [
                'type' => $p->type,
                'name' => $p->name,
                'audio_files' => array_column($p->audio_assets ?? [], 'filename'),
                'end_time' => $end,
            ];
            break;
        }
    }

    $serverTimestamp = now()->timestamp * 1000;

    $dayNames = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    return view('welcome', [
        'schedules' => $schedules,
        'playlists' => $playlists,
        'activePlaylist' => $activePlaylist,
        'serverTimestamp' => $serverTimestamp,
        'dayName' => $dayNames[$dayOfWeek],
        'todayDate' => $today->format('d F Y'),
        'isSchoolDay' => $isSchoolDay,
        'schoolStatus' => $schoolStatus,
        'firstBell' => $firstBell,
        'lastBell' => $lastBell,
        'reverbKey' => config('broadcasting.connections.reverb.key'),
        'reverbHost' => request()->getHost(),
        'reverbPort' => config('broadcasting.connections.reverb.options.port'),
        'reverbScheme' => config('broadcasting.connections.reverb.options.scheme'),
    ]);
});

Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Staff + Admin (read-only + bell darurat + dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/bell-darurat', [AdminController::class, 'bellDarurat'])->name('bell.darurat');
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
    Route::get('/school-days', [AdminController::class, 'schoolDays'])->name('school-days');
    Route::get('/playlists', [\App\Http\Controllers\Admin\BellPlaylistController::class, 'index'])->name('playlists.index');
});

/*
|--------------------------------------------------------------------------
| Admin only (full CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/audio', [AdminController::class, 'audioIndex'])->name('audio.index');
    Route::post('/audio/upload', [AdminController::class, 'audioUpload'])->name('audio.upload');
    Route::delete('/audio/{filename}', [AdminController::class, 'audioDelete'])->name('audio.delete');
    Route::put('/audio/{filename}/edit', [AdminController::class, 'audioEdit'])->name('audio.edit');
    Route::put('/school-days', [AdminController::class, 'schoolDaysUpdate'])->name('school-days.update');
    Route::post('/schedules', [AdminController::class, 'schedulesStore'])->name('schedules.store');
    Route::post('/schedules/copy', [AdminController::class, 'schedulesCopy'])->name('schedules.copy');
    Route::post('/schedules/generate-default', [AdminController::class, 'schedulesGenerateDefault'])->name('schedules.generate-default');
    Route::delete('/schedules/reset', [AdminController::class, 'schedulesReset'])->name('schedules.reset');
    Route::delete('/schedules/day/{day}', [AdminController::class, 'schedulesDestroyDay'])->name('schedules.destroyDay');
    Route::put('/schedules/{schedule}', [AdminController::class, 'schedulesUpdate'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AdminController::class, 'schedulesDestroy'])->name('schedules.destroy');

    Route::resource('playlists', \App\Http\Controllers\Admin\BellPlaylistController::class)
        ->except(['show', 'index']);

    Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{user}/role', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('users.destroy');
});

Route::get('/audio/{filename}', function (string $filename) {
    $path = base_path('assets_audio/' . basename($filename));

    if (! file_exists($path)) {
        abort(404);
    }

    $mime = match (pathinfo($path, PATHINFO_EXTENSION)) {
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        default => 'audio/mpeg',
    };

    return response()->file($path, ['Content-Type' => $mime]);
})->where('filename', '.*');

Route::post('/api/playlist-finished', function (\Illuminate\Http\Request $request) {
    $type = $request->input('type');
    $name = $request->input('name');

    broadcast(new \App\Events\PlaylistFinished($type, $name));

    return response()->json(['status' => 'ok']);
});

Route::get('/api/emergency-bell', function () {
    $emergency = \Illuminate\Support\Facades\Cache::get('emergency_bell');
    if ($emergency) {
        \Illuminate\Support\Facades\Cache::forget('emergency_bell');
        return response()->json(['audio_file' => $emergency]);
    }
    return response()->json(['audio_file' => null]);
});

require __DIR__.'/auth.php';
