<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware('auth.cloud')->group(function () {
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
