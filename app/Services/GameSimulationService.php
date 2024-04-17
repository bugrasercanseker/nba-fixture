<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Events\GameEndedEvent;
use App\Events\GameStartedEvent;
use App\Events\NewAttackEvent;
use App\Events\NewPointEvent;
use App\Events\NewScoreEvent;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use JetBrains\PhpStorm\NoReturn;

class GameSimulationService
{
    private Team $attacker;
    private Team $defender;

    private Collection $homeTeamPlayers;
    private Collection $awayTeamPlayers;
    private Game $game;

    public function simulate(Game $game): void
    {
        $this->game = $game;

        $game->update(['status' => GameStatus::PLAYING]);

        event(new GameStartedEvent($game));

        $matchDurationSeconds = 48 * 5; // 48 minutes * 5 seconds per minute
        $updateIntervalSeconds = 5; // Update score every 5 seconds
        $attackTimeLimitSeconds = 24; // Maximum attack time

        $this->homeTeamPlayers = $game->home_team->players->shuffle()->take(5); // Get 5 players randomly for game
        $this->awayTeamPlayers = $game->away_team->players->shuffle()->take(5); // Get 5 players randomly for game

        // Create Stats for Game
        $game->stats()->create();

        // Jump the Ball
        $this->jumpBall(game: $game);

        for ($time = 0; $time < $matchDurationSeconds; $time += $updateIntervalSeconds) {
            $startTime = microtime(true);

            /**
             * Total Possessions = (Team A FGA + Team A FTA) + (Team B FGA + Team B FTA)
             * AVG Possessions = 15 seconds
             * In 1 minute interval, we can have possessions between 3 and 5
             * Reference: https://www.nba.com/stats/teams/traditional
             */
            $actionsPerInterval = rand(3, 5);

            for ($i = 0; $i < $actionsPerInterval; $i++) {
                // Simulate actions during each time interval
                $this->simulateAction();

                // Update possession for the next interval
                if ($time % $attackTimeLimitSeconds === 0) {
                    $this->switchAttacker();
                }
            }

            $endTime = microtime(true);
            $elapsedTime = $endTime - $startTime;

            // Calculate remaining time for the interval
            $sleepTime = $updateIntervalSeconds - $elapsedTime;

            /**
             * Find right way to do that, I think it is wrong. Maybe separate this into multiple jobs???
             */
            if ($sleepTime > 0) {
                usleep($sleepTime * 1000000); // Convert seconds to microseconds for usleep
            }

            $game->increment('minute');
        }

        $game->update(['status' => GameStatus::PLAYED]);

        event(new GameEndedEvent($game));
    }

    private function jumpBall(Game $game): void
    {
        $chance = rand(1, 100);

        // 65% change of start for home team
        if ($chance <= 65) {
            $this->attacker = $game->home_team;
            $this->defender = $game->away_team;
        } else {
            $this->attacker = $game->away_team;
            $this->defender = $game->home_team;
        }
    }

    private function simulateAttack(): void
    {
        if ($this->attacker->id === $this->game->home_team_id) {
            $this->game->stats()->increment('home_attack');
        } else {
            $this->game->stats()->increment('away_attack');
        }

        event(new NewAttackEvent(game: $this->game));
    }

    private function switchAttacker(): void
    {
        $temp = $this->attacker;
        $this->attacker = $this->defender;
        $this->defender = $temp;

        $this->simulateAttack();
    }

    #[NoReturn] private function switchPlayer(): void
    {
        if ($this->attacker->id === $this->game->home_team_id) {
            $this->substitutePlayer($this->game->home_team->players, $this->homeTeamPlayers);
        } else {
            $this->substitutePlayer($this->game->away_team->players, $this->awayTeamPlayers);
        }
    }

    private function simulateAction(): void
    {
        $chance = rand(1, 100);

        /**
         * In every possession players made 65% shot, 20% attacks turned over and 15% of possessions are ended with substitution
         * Reference: https://www.nba.com/stats/players/traditional
         */
        // Action Probabilities based on Pace Reference and desired Action Distribution
        $shotProbability = 0.65;
        $turnoverProbability = 0.2;
        $substitutionProbability = 0.15;

        // Total Probability for Actions (should sum to 100%)
        $totalActionProbability = $shotProbability + $turnoverProbability + $substitutionProbability;

        // Calculate Probability Thresholds as a percentage of total probability
        $shotThreshold = ($shotProbability * $totalActionProbability) * 100;
        $turnoverThreshold = ($shotThreshold + ($turnoverProbability * $totalActionProbability) * 100);

        if ($chance <= $shotThreshold) {
            $action = 'shot'; // Action within shot attempt probability range
        } elseif ($chance <= $turnoverThreshold) {
            $action = 'turnover'; // Action within turnover probability range
        } else {
            $action = 'substitution'; // Remaining probability for substitution
        }

        // Process the action
        switch ($action) {
            case 'shot':
                // Simulate shot attempt
                $this->simulateShot();
                break;
            case 'turnover':
                // Simulate turnover
                $this->switchAttacker();
                break;
            case 'substitution':
                // Simulate substitution
                $this->switchPlayer();
                break;
        }
    }

    private function simulateShot(): void
    {
        $isHomeTeamShooting = $this->attacker->id === $this->game->home_team_id;

        /**
         * Average Success rate of NBA Players between %47.9 and 46.1
         * Reference: https://www.nba.com/stats/players/traditional
         *
         * I decide to use 46.5 and +%5 for home team.
         */
        $shotSuccessProbability = ($isHomeTeamShooting) ? 51.5 : 46.5; // Home Team has %5 more advance
        $chance = rand(1, 100);

        $scored = $chance <= $shotSuccessProbability;

        if ($isHomeTeamShooting) {
            $player = $this->homeTeamPlayers->shuffle()->first();
            $assistedBy = $this->homeTeamPlayers->where('id', '!=', $player->id)->shuffle()->first();
        } else {
            $player = $this->awayTeamPlayers->shuffle()->first();
            $assistedBy = $this->awayTeamPlayers->where('id', '!=', $player->id)->shuffle()->first();
        }

        $points = $this->simulateShotPoints();

        $this->simulatePlayerAttempted(player: $player, points: $points);

        if ($scored) {
            if ($this->attacker->id === $this->game->home_team_id) {
                $this->game->stats()->increment('home_score', $points); // Update home team score
            } else {
                $this->game->stats()->increment('away_score', $points); // Update away team score
            }

            $this->simulatePlayerScored(player: $player, assistedBy: $assistedBy, points: $points);
            $this->switchAttacker();
        } else {
            $chance = rand(1, 100);

            if ($chance <= 75) {
                $this->switchAttacker(); // %75 chance to rebound
            }
        }
    }

    private function simulateShotPoints(): int
    {
        $chance = rand(1, 100);

        return $chance <= 30 ? 3 : 2; // %30 change to 3 points
    }

    private function simulatePlayerAttempted(Player $player, int $points): void
    {
        $stats = $player->stats()->firstOrCreate([
            'game_id' => $this->game->id
        ]);

        if ($points === 2) {
            $stats->increment('two_point_attempt');
        } else {
            $stats->increment('three_point_attempt');
        }
    }

    private function simulatePlayerScored(Player $player, Player $assistedBy, int $points): void
    {
        $stats = $player->stats()->firstOrCreate([
            'game_id' => $this->game->id
        ]);

        if ($points === 2) {
            $stats->increment('two_point_success');
        } else {
            $stats->increment('three_point_success');
        }

        $assistedByStats = $assistedBy->stats()->firstOrCreate([
            'game_id' => $this->game->id
        ]);

        $assistedByStats->increment('assist_count');

        event(new NewScoreEvent(game: $this->game));
        event(new NewPointEvent(game: $this->game, player: $player, assistedBy: $assistedBy));
    }

    #[NoReturn] private function substitutePlayer($allPlayers, &$teamPlayers): void
    {
        // Randomly select a player to substitute
        $playerToSubstituteIndex = rand(0, count($teamPlayers) - 1);
        $teamPlayers->splice($playerToSubstituteIndex, 1)->first();

        // Simulate substitution by adding a new random player
        $newPlayer = $allPlayers->whereNotIn('id', $teamPlayers->pluck('id')->toArray())->shuffle()->first();
        $teamPlayers->push($newPlayer);
    }
}
