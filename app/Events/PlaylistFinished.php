<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class PlaylistFinished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $type,
        public string $name,
        public ?string $action = null,
        public int $action_delay = 0,
        public ?string $custom_command = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('bell')];
    }
}
