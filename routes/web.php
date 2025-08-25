<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\StandenController;

Route::get('/', [StandenController::class, 'index'])->name('home');
Route::group(['middleware' => 'auth'], function () {
    Route::resource('games', GameController::class);
    Route::get('/team', [TeamController::class, 'myTeam'])
        ->middleware('no-admin-on-team')
        ->name('my-team');
});

Route::post('splitpoules', [StandenController::class, 'splitPoulesIntoWinnersAndLosers'])->name('splitpoules');

Route::get('schemaAanmaken', [StandenController::class, 'scheduleGroupPhase'])->middleware('auth')->name('schemaAanmaken');


Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
