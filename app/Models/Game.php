<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'week',
        'minute',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class
        ];
    }

    public function home_team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function away_team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(GameStat::class);
    }

    public function player_stats(): HasMany
    {
        return $this->hasMany(PlayerStat::class, 'game_id', 'id');
    }
}
