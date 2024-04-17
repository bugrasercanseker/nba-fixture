<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\GameSimulationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulateGameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(private readonly Game $game)
    {
    }

    public function handle(GameSimulationService $gameSimulationService): void
    {
        $gameSimulationService->simulate(game: $this->game);
    }
}
