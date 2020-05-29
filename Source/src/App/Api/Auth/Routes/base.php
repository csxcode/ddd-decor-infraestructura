<?php

use App\Http\Controllers\Api\AuthController;

Route::prefix('auth')->group(function(){
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('ping', [AuthController::class, 'ping']);
});
