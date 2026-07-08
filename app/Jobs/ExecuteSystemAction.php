<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExecuteSystemAction implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public string $action,
        public ?string $command = null,
    ) {}

    public function handle(): void
    {
        $cmd = match ($this->action) {
            'shutdown' => 'sudo /sbin/shutdown -h now',
            'restart' => 'sudo /sbin/reboot',
            'custom' => $this->command,
            default => null,
        };

        if (!$cmd) {
            Log::warning("Unknown system action: {$this->action}");
            return;
        }

        Log::info("Executing system action: {$this->action} -> {$cmd}");
        exec($cmd . ' 2>&1', $output, $exitCode);
        Log::info("System action result: exit={$exitCode}, output=" . implode("\n", $output));
    }
}
