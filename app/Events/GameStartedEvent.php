<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStartedEvent implements ShouldBroadcastNow
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
        return 'GameStartedEvent';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game-started')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game' => $this->game,
        ];
    }
}
