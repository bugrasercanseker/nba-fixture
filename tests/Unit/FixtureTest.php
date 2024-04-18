<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('is first week fixture page loaded', function () {
    $this->get('/fixture?week=1')
        ->assertInertia(fn(Assert $page) => $page
            ->component('Fixture/Index')
        );
});

it('can generate fixture', function () {
    $this->seed(\Database\Seeders\TeamAndPlayerSeeder::class);

    $this->post('fixture/generate')
        ->assertRedirect('fixture?week=1');
});

it('is first week fixture has games', function () {
    $this->seed(\Database\Seeders\TeamAndPlayerSeeder::class);

    $this->followingRedirects()
        ->post('fixture/generate')
        ->assertOk()
        ->assertInertia(fn(Assert $page) => $page
            ->component('Fixture/Index')
            ->where('errors', [])
            ->has('fixture')
            ->has('fixture.0', fn(Assert $page) => $page
                ->has('id')
                ->has('home_team_id')
                ->has('away_team_id')
                ->has('home_team', 4)
                ->has('away_team', 4)
                ->has('week')
                ->has('minute')
                ->has('status')
                ->has('stats')
                ->has('player_stats')
            )
            ->has('playerStats')
            ->has('minWeek')
            ->has('maxWeek')
            ->has('week')
        );
});
