<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware(['throttle:api'])->group(function () {
    Route::get('/ping', fn () => response()->json(['pong' => true]));
});
