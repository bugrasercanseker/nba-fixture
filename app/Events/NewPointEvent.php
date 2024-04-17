<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPointEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    private Player $player;
    private Player $assistedBy;
    private int $points;
    private Game $game;

    /**
     * @param Game $game
     * @param Player $player
     * @param Player $assistedBy
     */
    public function __construct(Game $game, Player $player, Player $assistedBy)
    {
        $this->player = $player;
        $this->assistedBy = $assistedBy;
        $this->game = $game;
    }

    public function broadcastAs(): string
    {
        return 'NewPointEvent';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('new-point')
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'player' => $this->player->stats()->where('game_id', $this->game->id)->first()->load('player.team')->toArray(),
            'assistedBy' => $this->assistedBy->stats()->where('game_id', $this->game->id)->first()->load('player.team')->toArray()
        ];
    }
}
