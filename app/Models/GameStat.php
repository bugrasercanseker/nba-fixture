<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameStat extends Model
{
    protected $fillable = [
        'game_id',
        'home_attack',
        'home_score',
        'away_attack',
        'away_score',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
