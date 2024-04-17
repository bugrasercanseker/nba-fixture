<?php

namespace App\Interfaces;

use App\Models\Game;
use Illuminate\Support\Collection;

interface GameRepositoryInterface
{
    public function getAllGames(): Collection;

    public function getPendingGames(): Collection;

    public function getNextWeekGames(): ?Collection;

    public function getGamesByWeek(int $week): Collection;

    public function getGameByTeamId(int $id): Collection;

    public function getPlayerStatsByGame(int $id): \Illuminate\Database\Eloquent\Collection;

    public function create(int $home_team, int $away_team, int $week): Game;
}
