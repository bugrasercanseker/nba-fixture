<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewScoreEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels, InteractsWithSockets;

    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function broadcastAs(): string
    {
        return 'NewScoreEvent';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('new-score')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game' => $this->game->load('stats')->toArray()
        ];
    }
}
