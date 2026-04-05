<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BootstrapController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

// ── Auth (only when AUTH_PROVIDER=passport) ──────────────────────────

if (config('chukka.auth_provider') === 'passport') {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::get('/auth/redirect', [LoginController::class, 'redirect'])->name('auth.redirect');
    Route::get('/auth/callback', [LoginController::class, 'callback'])->name('auth.callback');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
    Route::get('/dev-login/{user}', [LoginController::class, 'devLogin'])->name('dev.login');
}

// ── App (auth required when enabled, bootstrap required always) ──────

Route::middleware('auth.chukka')->group(function () {
    // Bootstrap (no EnsureBootstrapped — this IS the setup)
    Route::get('/bootstrap', [BootstrapController::class, 'show'])->name('bootstrap.show');
    Route::post('/bootstrap', [BootstrapController::class, 'store'])->name('bootstrap.store');

    // Simulation (requires bootstrap)
    Route::middleware('bootstrapped')->group(function () {
        Route::get('/', [SimulationController::class, 'setup'])->name('simulation.setup');
        Route::post('/simulation', [SimulationController::class, 'store'])->name('simulation.store')->middleware('throttle:3,60');
        Route::get('/simulation/{session}', [SimulationController::class, 'playback'])->name('simulation.playback');
        Route::post('/simulation/{session}/play', [SimulationController::class, 'play'])->name('simulation.play');
        Route::post('/simulation/{session}/pause', [SimulationController::class, 'pause'])->name('simulation.pause');
        Route::post('/simulation/{session}/stop', [SimulationController::class, 'stop'])->name('simulation.stop');
        Route::post('/simulation/{session}/speed', [SimulationController::class, 'speed'])->name('simulation.speed');
        Route::post('/simulation/{session}/inject', [SimulationController::class, 'inject'])->name('simulation.inject');
        Route::get('/simulation/{session}/state', [SimulationController::class, 'state'])->name('simulation.state');
    });
});
