<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameEndedEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    private Game $game;

    /**
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function broadcastAs(): string
    {
        return 'GameEndedEvent';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game-ended')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game' => $this->game,
        ];
    }
}
