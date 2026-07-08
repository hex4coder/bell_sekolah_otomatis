<?php

namespace App\Console\Commands;

use App\Events\BellPlayed;
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
        $now = now()->format('H:i');

        $schoolDay = SchoolDay::where('day_of_week', $today)->where('is_active', true)->exists();

        if (!$schoolDay) {
            return Command::SUCCESS;
        }

        $schedules = BellSchedule::where('day_of_week', $today)
            ->where('is_active', true)
            ->where('time', $now)
            ->whereNotNull('audio_file')
            ->get();

        foreach ($schedules as $schedule) {
            $cacheKey = 'bell_fired_' . $schedule->id . '_' . now()->format('Ymd');

            if (Cache::add($cacheKey, true, now()->endOfDay())) {
                broadcast(new BellPlayed(
                    $schedule->id,
                    $schedule->name,
                    $schedule->time->format('H:i'),
                    $schedule->audio_file,
                ));
                Log::info("Bell fired: {$schedule->name} at {$now}");
            }
        }

        return Command::SUCCESS;
    }
}
