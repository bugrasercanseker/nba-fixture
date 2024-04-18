<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);


it('can seed teams and players', function () {
    $this->seed(\Database\Seeders\TeamAndPlayerSeeder::class);

    $teamsCount = \App\Models\Team::count();
    $playersCount = \App\Models\Player::count();

    expect($teamsCount)->toBeGreaterThan(0)
        ->and($playersCount)->toBeGreaterThan(0);
});
