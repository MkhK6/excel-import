<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ImportProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $progressData) {}

    public function broadcastOn()
    {
        return new Channel('import-progress.' . $this->progressData['progress_key']);
    }

    public function broadcastAs()
    {
        return 'import.progress';
    }
}
