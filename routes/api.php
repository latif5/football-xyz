<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GoalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('api.health');

// Public routes
Route::middleware(['throttle:api'])->group(function () {
    Route::get('/ping', fn () => response()->json(['pong' => true]));
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::put('/teams/{team}', [TeamController::class, 'update']);
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']);
    Route::post('/teams/{team}/restore', [TeamController::class, 'restore']);

    Route::get('/teams/{team}/players', [PlayerController::class, 'index']);
    Route::post('/teams/{team}/players', [PlayerController::class, 'store']);
    Route::put('/teams/{team}/players/{player}', [PlayerController::class, 'update']);
    Route::delete('/teams/{team}/players/{player}', [PlayerController::class, 'destroy']);
    Route::post('/teams/{team}/players/{player}/restore', [PlayerController::class, 'restore']);

    Route::get('/matches', [MatchController::class, 'index']);
    Route::post('/matches', [MatchController::class, 'store']);
    Route::get('/matches/{match}', [MatchController::class, 'show']);
    Route::put('/matches/{match}', [MatchController::class, 'update']);
    Route::delete('/matches/{match}', [MatchController::class, 'destroy']);
    Route::post('/matches/{match}/restore', [MatchController::class, 'restore']);
    Route::post('/matches/{match}/finalize', [MatchController::class, 'finalize']);

    Route::get('/matches/{match}/goals', [GoalController::class, 'index']);
    Route::post('/matches/{match}/goals', [GoalController::class, 'store']);
    Route::put('/matches/{match}/goals/{goal}', [GoalController::class, 'update']);
    Route::delete('/matches/{match}/goals/{goal}', [GoalController::class, 'destroy']);
    Route::post('/matches/{match}/goals/{goal}/restore', [GoalController::class, 'restore']);

    Route::get('/matches/{match}/report', [ReportController::class, 'matchReport']);
    Route::get('/matches/{match}/report.pdf', [ReportController::class, 'matchReportPdf']);
    Route::get('/reports/top-scorers', [ReportController::class, 'topScorers']);
    Route::get('/reports/team-wins', [ReportController::class, 'teamWins']);
});
