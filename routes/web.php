<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', [GameController::class, 'index'])->name('game.index');
Route::post('/start', [GameController::class, 'start'])->name('game.start');
Route::post('/guess', [GameController::class, 'guess'])->name('game.guess');
Route::post('/restart', [GameController::class, 'restart'])->name('game.restart');
