<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
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

    if (!$isSchoolDay || $schedules->isEmpty()) {
        $schoolStatus = 'Libur';
    } elseif ($firstBell && $nowTime < $firstBell) {
        $schoolStatus = 'Belum masuk';
    } elseif ($lastBell && $nowTime > $lastBell) {
        $schoolStatus = 'Selesai';
    } else {
        $schoolStatus = 'Berlangsung';
    }

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
        'dayName' => $dayNames[$dayOfWeek],
        'todayDate' => $today->format('d F Y'),
        'isSchoolDay' => $isSchoolDay,
        'schoolStatus' => $schoolStatus,
        'firstBell' => $firstBell,
        'lastBell' => $lastBell,
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/audio', [AdminController::class, 'audioIndex'])->name('audio.index');
    Route::post('/audio/upload', [AdminController::class, 'audioUpload'])->name('audio.upload');
    Route::delete('/audio/{filename}', [AdminController::class, 'audioDelete'])->name('audio.delete');
    Route::put('/audio/{filename}/edit', [AdminController::class, 'audioEdit'])->name('audio.edit');
    Route::get('/school-days', [AdminController::class, 'schoolDays'])->name('school-days');
    Route::put('/school-days', [AdminController::class, 'schoolDaysUpdate'])->name('school-days.update');
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
    Route::post('/schedules', [AdminController::class, 'schedulesStore'])->name('schedules.store');
    Route::post('/schedules/copy', [AdminController::class, 'schedulesCopy'])->name('schedules.copy');
    Route::delete('/schedules/reset', [AdminController::class, 'schedulesReset'])->name('schedules.reset');
    Route::delete('/schedules/day/{day}', [AdminController::class, 'schedulesDestroyDay'])->name('schedules.destroyDay');
    Route::put('/schedules/{schedule}', [AdminController::class, 'schedulesUpdate'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AdminController::class, 'schedulesDestroy'])->name('schedules.destroy');
    Route::post('/bell-darurat', [AdminController::class, 'bellDarurat'])->name('bell.darurat');
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

Route::get('/api/emergency-bell', function () {
    $emergency = \Illuminate\Support\Facades\Cache::get('emergency_bell');
    if ($emergency) {
        \Illuminate\Support\Facades\Cache::forget('emergency_bell');
        return response()->json(['audio_file' => $emergency]);
    }
    return response()->json(['audio_file' => null]);
});

require __DIR__.'/auth.php';
