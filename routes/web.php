<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return inertia()->render('Index');
});

Route::prefix('fixture')->name('fixture')->group(function () {
    Route::controller(\App\Http\Controllers\FixtureController::class)->group(function () {
        Route::get('/', 'index')->name('.index');
        Route::post('generate', 'generate')->name('.generate');
        Route::post('simulate', 'simulate')->name('.simulate');
    });
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
