<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Team;
use App\Repositories\GameEloquentRepository;
use Illuminate\Support\Collection;

class GameService
{
    private Team $team;
    private GameEloquentRepository $gameRepository;

    /**
     * @param Team $team
     * @param GameEloquentRepository $gameRepository
     */
    public function __construct(Team $team, GameEloquentRepository $gameRepository)
    {
        $this->team = $team;
        $this->gameRepository = $gameRepository;
    }

    public function check(): bool
    {
        return $this->gameRepository->query()->exists();
    }

    public function getMinWeek(): int
    {
        if ($this->gameRepository->query()->exists()) {
            return $this->gameRepository->query()->min('week');
        } else {
            return 0;
        }
    }

    public function getMaxWeek(): int
    {
        if ($this->gameRepository->query()->exists()) {
            return $this->gameRepository->query()->max('week');
        } else {
            return 0;
        }
    }

    public function getAllGames(): Collection
    {
        return $this->gameRepository->getAllGames();
    }

    public function getPendingGames(): Collection
    {
        return $this->gameRepository->getPendingGames();
    }

    public function getNextWeekGames(): ?Collection
    {
        return $this->gameRepository->getNextWeekGames();
    }

    public function getGamesByWeek(int $week): Collection
    {
        return $this->gameRepository->getGamesByWeek(week: $week);
    }

    public function getGameByTeamId(int $id): Collection
    {
        return $this->gameRepository->getGameByTeamId(id: $id);
    }

    public function generate(): array
    {
        $teamIds = $this->team->pluck('id')->shuffle()->toArray();
        $numberOfWeeks = $this->getNumberOfWeeks(count($teamIds));
        $numberOfWeeklyFixtures = $this->getNumberOfWeeklyFixtures(count($teamIds));
        $fixtures = $this->makeFixtures($teamIds);
        $weeklyFixtures = $this->makeWeeklyFixtures($numberOfWeeks, $numberOfWeeklyFixtures, $fixtures);

        $this->createGames($weeklyFixtures);

        return $weeklyFixtures;
    }

    private function getNumberOfWeeks(int $count): int
    {
        return $count % 2 == 0 ? ($count - 1) * 2 : ($count - 1) / 2;
    }

    private function getNumberOfWeeklyFixtures(int $count): int
    {
        return $count % 2 == 0 ? $count / 2 : ($count - 1) / 2;
    }

    private function makeFixtures(array $teamIds): array
    {
        $fixtures = [];

        foreach ($teamIds as $homeTeam) {
            foreach ($teamIds as $awayTeam) {
                if ($homeTeam !== $awayTeam) {
                    $fixtures[] = ['home' => $homeTeam, 'away' => $awayTeam];
                }
            }
        }

        return $fixtures;
    }

    private function makeWeeklyFixtures(int $numberOfWeeks, int $numberOfWeeklyFixtures, array $fixtures): array
    {
        $weeklyFixtures = [];

        for ($week = 1; $week <= $numberOfWeeks; $week++) {
            for ($i = 1; $i <= $numberOfWeeklyFixtures; $i++) {
                foreach ($fixtures as &$fixture) {
                    if (!isset($weeklyFixtures[$week])) {
                        $weeklyFixtures[$week] = [];
                    }

                    if (!$this->isMatchDuplicatedInWeek($fixture, $weeklyFixtures[$week])) {
                        $weeklyFixtures[$week][] = [
                            'home' => $fixture['home'],
                            'away' => $fixture['away'],
                            'week' => $week,
                            'status' => GameStatus::PENDING,
                        ];

                        $fixture['matched'] = true;
                        break;
                    }
                }
            }
        }

        return collect($weeklyFixtures)->flatten(1)->toArray();
    }

    private function isMatchDuplicatedInWeek(array $fixture, array $weeklyFixtures): bool
    {
        foreach ($weeklyFixtures as $weeklyFixture) {
            if (($fixture['home'] === $weeklyFixture['home'] || $fixture['home'] === $weeklyFixture['away']) ||
                ($fixture['away'] === $weeklyFixture['away'] || $fixture['away'] === $weeklyFixture['home'])) {
                return true;
            }
        }

        return false;
    }

    private function createGames(array $weeklyFixtures): void
    {
        foreach ($weeklyFixtures as $fixture) {
            $this->gameRepository->create(
                home_team: $fixture['home'],
                away_team: $fixture['away'],
                week: $fixture['week']
            );
        }
    }
}
