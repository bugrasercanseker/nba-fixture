<?php

namespace App\Http\Controllers;

use App\Http\Requests\Fixture\GetRequest;
use App\Jobs\SimulateGameJob;
use App\Services\GameService;
use App\Services\GameSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Inertia\Response;

class FixtureController extends Controller
{
    private GameService $gameService;
    /**
     * @param GameService $gameService
     * @param GameSimulationService $gameSimulationService
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function index(GetRequest $request): Response
    {
        // Get Min and Max Week for switching between weeks
        $minWeek = $this->gameService->getMinWeek();
        $maxWeek = $this->gameService->getMaxWeek();

        $week = intval($request->get('week'));
        $fixture = $this->gameService->getGamesByWeek(week: $week);
        $playerStats = $fixture->pluck('player_stats')->flatten(1)->values();

        return inertia()->render(
            'Fixture/Index',
            compact('fixture', 'playerStats', 'week', 'minWeek', 'maxWeek')
        );
    }

    public function generate(): RedirectResponse
    {
        $this->gameService->generate();

        return to_route('fixture.index', ['week' => 1]);
    }

    public function simulate(Request $request)
    {
        $week = $request->get('week');
        $fixture = $this->gameService->getGamesByWeek(week: $week);

        $jobs = [];
        foreach ($fixture as $game) {
            $jobs[] = new SimulateGameJob($game);
        }

        Bus::batch($jobs)->onQueue('high')->dispatch();

        return back();
    }
}
