<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStat extends Model
{
    protected $fillable = [
        'player_id',
        'game_id',
        'assist_count',
        'two_point_attempt',
        'two_point_success',
        'three_point_attempt',
        'three_point_success',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
