<?php

namespace App\Repositories;

use App\Enums\GameStatus;
use App\Interfaces\GameRepositoryInterface;
use App\Models\Game;
use App\Models\PlayerStat;
use Illuminate\Support\Collection;

class GameEloquentRepository implements GameRepositoryInterface
{
    private Game $game;

    /**
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function query()
    {
        return $this->game->query();
    }

    public function getAllGames(): Collection
    {
        return $this->game->with('home_team', 'away_team')->orderBy('week')->get()->groupBy('week');
    }

    public function getPendingGames(): Collection
    {
        return $this->game->with('home_team', 'away_team')->where('status', GameStatus::PENDING)->get()->groupBy('week');
    }

    public function getNextWeekGames(): ?Collection
    {
        return $this->getPendingGames()->groupBy('week')->first();
    }

    public function getGamesByWeek(int $week): Collection
    {
        /**
         * We can get game based player stats with player_stats relation. We can do it as a feature.
         */
        return $this->game->with('home_team', 'away_team', 'stats', 'player_stats.player.team')->where('week', $week)->get();
    }

    public function getGameByTeamId(int $id): Collection
    {
        return $this->game->where('home_team_id', $id)->orWhere('away_team_id', $id)->get();
    }

    public function getPlayerStatsByGame(int $id): \Illuminate\Database\Eloquent\Collection
    {
        return $this->game->findOrFail($id)->playerStats()->with('player.team')->get();
    }

    public function create(int $home_team, int $away_team, int $week): Game
    {
        return $this->game->create([
            'home_team_id' => $home_team,
            'away_team_id' => $away_team,
            'week' => $week,
        ]);
    }
}
