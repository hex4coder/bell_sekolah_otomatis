<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ShutdownRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $action,
        public ?string $command = null,
        public int $delay = 0,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('bell')];
    }
}
