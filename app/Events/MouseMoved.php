<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MouseMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public $position;

    public function __construct($payload)
    {
        $this->userId = $payload['userId'];
        $this->position = $payload['position'];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('mouse-movement'),
        ];
    }
}
