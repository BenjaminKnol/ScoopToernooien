<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PlayerAdminController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TeamAssignmentController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\StandenController;

Route::get('/', [StandenController::class, 'index'])->name('home');

// Language change endpoint (web session + optional user profile)
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::group(['middleware' => 'auth'], function () {
    Route::resource('games', GameController::class)->middleware('admin');
    Route::get('/team', [TeamController::class, 'myTeam'])
        ->middleware('no-admin-on-team')
        ->name('my-team');

    // Team posts
    Route::post('/team/posts', [\App\Http\Controllers\TeamPostController::class, 'storeThread'])->name('team.posts.store');
    Route::post('/team/posts/{thread}/reply', [\App\Http\Controllers\TeamPostController::class, 'storeReply'])->name('team.posts.reply');

    // Team admin (CRUD from dashboard)
    Route::post('/teams', [\App\Http\Controllers\TeamController::class, 'store'])->middleware('admin')->name('teams.store');
    Route::put('/teams/{team}', [\App\Http\Controllers\TeamController::class, 'update'])->middleware('admin')->name('teams.update');
    Route::delete('/teams/{team}', [\App\Http\Controllers\TeamController::class, 'destroy'])->middleware('admin')->name('teams.destroy');

    // Players admin (CSV import)
    Route::post('/players/import', [PlayerAdminController::class, 'import'])->middleware('admin')->name('players.import');
    Route::put('/players/{player}', [PlayerAdminController::class, 'update'])->middleware('admin')->name('players.update');
    Route::post('/players', [PlayerController::class, 'store'])->middleware('admin')->name('players.store');
    Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->middleware('admin')->name('players.destroy');
});

// Dashboard for admins
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard');

// Schedule generation for admins
Route::get('dashboard/generateSchedule', [ScheduleController::class, 'create'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard.generateSchedule');
Route::post('dashboard/generateSchedule', [ScheduleController::class, 'store'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard.generateSchedule.store');
Route::post('dashboard/generateSchedule/apply', [ScheduleController::class, 'apply'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard.generateSchedule.apply');

// Team auto-assignment for admins
Route::get('dashboard/autoAssignTeams', [TeamAssignmentController::class, 'create'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard.autoAssignTeams');
Route::post('dashboard/autoAssignTeams', [TeamAssignmentController::class, 'store'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard.autoAssignTeams.store');
Route::post('dashboard/autoAssignTeams/apply', [TeamAssignmentController::class, 'apply'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard.autoAssignTeams.apply');

// Settings for users
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
