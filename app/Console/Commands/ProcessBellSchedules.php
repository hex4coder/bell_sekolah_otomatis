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

        $schoolDay = SchoolDay::where('day_of_week', $today)->where('is_active', true)->exists();

        if (!$schoolDay) {
            return Command::SUCCESS;
        }

        $now = now();
        $current = $now->format('H:i');
        $twoMinutesAgo = $now->subMinutes(2)->format('H:i');

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

        return Command::SUCCESS;
    }
}
