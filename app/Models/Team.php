<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'short_name',
        'simple_name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
