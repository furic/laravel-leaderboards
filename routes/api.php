<?php

use Illuminate\Support\Facades\Route;
use Furic\Leaderboards\Http\Controllers\LeaderboardController;

Route::prefix('api')->group(function() {
	Route::get('leaderboards/{id}', [LeaderboardController::class, 'show']);
    Route::get('leaderboards/{id}/current', [LeaderboardController::class, 'showCurrent']);
    Route::get('leaderboards/{id}/score-sums', [LeaderboardController::class, 'showScoreSums']);
    Route::get('leaderboards/{id}/highscores', [LeaderboardController::class, 'showHighscores']);
    Route::get('leaderboards/{id}/rewards', [LeaderboardController::class, 'showRewards']);
    Route::post('leaderboards/{id}/score', [LeaderboardController::class, 'updateScore']);
    Route::post('leaderboards/{id}/reward', [LeaderboardController::class, 'updateReward']);
});