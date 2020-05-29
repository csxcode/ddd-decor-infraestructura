<?php

use App\Admin\Auth\Controllers\LoginController;
use App\Admin\Dashboard\Controllers\DashboardController;

// Public 
Route::prefix('auth')->group(function(){
    Route::get('login', [LoginController::class, 'show']);
    Route::post('login', [LoginController::class, 'login']);
    Route::get('logout', [LoginController::class, 'logout']);
});

// Protected
Route::group(['middleware' => 'auth.web'], function () {
      
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/home', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    //Route::get('global/ajax/get_branches_by_store', [GlobalController::class, 'GetBranchesByStore'])->name('global.ajax.get_branches_by_store');
    
    Route::resource('users', 'UserController');
});
