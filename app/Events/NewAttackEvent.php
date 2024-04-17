<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewAttackEvent implements ShouldBroadcastNow
{
    use Dispatchable;

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
        return 'NewAttackEvent';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('new-attack')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game' => $this->game->load('stats')->toArray()
        ];
    }
}
