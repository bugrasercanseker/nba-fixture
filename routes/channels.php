<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('new-attack', function () {
    return true;
});

Broadcast::channel('new-score', function () {
    return true;
});

Broadcast::channel('new-point', function () {
    return true;
});

Broadcast::channel('game-started', function () {
    return true;
});

Broadcast::channel('game-ended', function () {
    return true;
});
