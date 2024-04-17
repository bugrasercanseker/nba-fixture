<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TeamAndPlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Storage::disk('local')->json('files/nba-teams.json');

        foreach ($teams as $team) {
            $t = Team::factory([
                'name' => $team['name'],
                'short_name' => $team['short_name'],
                'simple_name' => $team['simple_name'],
            ])->create();

            foreach ($team['players'] as $player) {
                $player['team_id'] = $t['id'];
                Player::factory($player)->create();
            }
        }
    }
}
