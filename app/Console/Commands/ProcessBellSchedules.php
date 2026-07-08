<?php

namespace App\Console\Commands;

use App\Events\BellPlayed;
use App\Events\PlaylistStarted;
use App\Models\BellPlaylist;
use App\Models\BellSchedule;
use App\Models\SchoolDay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessBellSchedules extends Command
{
    protected $signature = 'app:process-bell-schedules';
    protected $description = 'Process and fire bell schedules for the current minute';

    public function handle()
    {
        $today = now()->dayOfWeek;

        $schoolDay = SchoolDay::where('day_of_week', $today)->where('is_active', true)->exists();

        if (!$schoolDay) {
            return Command::SUCCESS;
        }

        $now = now();
        $current = $now->format('H:i');
        $twoMinutesAgo = $now->subMinutes(2)->format('H:i');

        $this->processBellSchedules($today, $twoMinutesAgo, $current);
        $this->processPlaylists($today, $current);

        return Command::SUCCESS;
    }

    private function processBellSchedules(int $today, string $twoMinutesAgo, string $current): void
    {
        $schedules = BellSchedule::where('day_of_week', $today)
            ->where('is_active', true)
            ->whereBetween('time', [$twoMinutesAgo, $current])
            ->whereNotNull('audio_file')
            ->get();

        foreach ($schedules as $schedule) {
            $schedTime = $schedule->time->format('H:i');
            $cacheKey = 'bell_fired_' . $schedule->id . '_' . now()->format('Ymd');

            if (Cache::add($cacheKey, true, now()->endOfDay())) {
                broadcast(new BellPlayed(
                    $schedule->id,
                    $schedule->name,
                    $schedTime,
                    $schedule->audio_file,
                ));
                Log::info("Bell fired: {$schedule->name} at {$schedTime}");
            }
        }
    }

    private function processPlaylists(int $today, string $current): void
    {
        $this->checkEmergencyClosing($current);

        $playlists = BellPlaylist::where('is_active', true)->orderBy('order')->get();

        foreach ($playlists as $playlist) {
            $cacheKey = 'playlist_fired_' . $playlist->id . '_' . now()->format('Ymd');

            if (Cache::has($cacheKey)) {
                continue;
            }

            if (!$this->playlistAppliesToday($playlist, $today, $current)) {
                continue;
            }

            $audioFiles = array_column($playlist->audio_assets ?? [], 'filename');

            if (empty($audioFiles)) {
                continue;
            }

            Cache::put($cacheKey, true, now()->endOfDay());

            $endTime = $playlist->time_range_end?->format('H:i');

            broadcast(new PlaylistStarted(
                $playlist->type,
                $playlist->name,
                $audioFiles,
                $endTime,
            ));

            Log::info("Playlist started: {$playlist->name} ({$playlist->type}) at {$current}");
        }
    }

    private function checkEmergencyClosing(string $current): void
    {
        $triggerAt = Cache::get('emergency_closing_at');
        if (!$triggerAt || now()->timestamp < $triggerAt) {
            return;
        }

        $playlistId = Cache::get('emergency_closing_id');
        if (!$playlistId) {
            Cache::forget('emergency_closing_at');
            return;
        }

        $playlist = BellPlaylist::find($playlistId);
        if (!$playlist || !$playlist->is_active) {
            Cache::forget('emergency_closing_at');
            Cache::forget('emergency_closing_id');
            return;
        }

        $audioFiles = array_column($playlist->audio_assets ?? [], 'filename');
        if (empty($audioFiles)) {
            return;
        }

        $endTime = $playlist->time_range_end?->format('H:i');

        broadcast(new PlaylistStarted(
            $playlist->type,
            $playlist->name,
            $audioFiles,
            $endTime,
        ));

        Log::info("Playlist started (emergency closing): {$playlist->name} at {$current}");

        Cache::put('playlist_fired_' . $playlist->id . '_' . now()->format('Ymd'), true, now()->endOfDay());
        Cache::forget('emergency_closing_at');
        Cache::forget('emergency_closing_id');
    }

    private function playlistAppliesToday(BellPlaylist $playlist, int $today, string $current): bool
    {
        $days = $playlist->day_of_week;
        if (!empty($days) && !in_array($today, $days)) {
            return false;
        }

        $schedules = BellSchedule::where('day_of_week', $today)
            ->where('is_active', true)
            ->orderBy('time')
            ->get();

        if ($schedules->isEmpty()) {
            return false;
        }

        $firstBell = $schedules->first()->time->format('H:i');
        $lastBell = $schedules->last()->time->format('H:i');

        if ($playlist->type === 'opening') {
            $start = $playlist->time_range_start?->format('H:i') ?? $this->subMinutes($firstBell, 15);
            $end = $playlist->time_range_end?->format('H:i') ?? $firstBell;
        } else {
            $start = $playlist->time_range_start?->format('H:i') ?? $lastBell;
            $end = $playlist->time_range_end?->format('H:i') ?? $this->addMinutes($lastBell, 15);
        }

        return $current >= $start && $current <= $end;
    }

    private function subMinutes(string $time, int $mins): string
    {
        return now()->setTimeFromTimeString($time)->subMinutes($mins)->format('H:i');
    }

    private function addMinutes(string $time, int $mins): string
    {
        return now()->setTimeFromTimeString($time)->addMinutes($mins)->format('H:i');
    }
}
