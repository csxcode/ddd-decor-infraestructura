<?php

use Illuminate\Support\Facades\Route;

// Public
require __DIR__.'/auth.php';

// Protected
Route::group(['middleware' => 'auth.api'], function() {
    require __DIR__.'/checklists.php';
    require __DIR__.'/tickets.php';
    Route::apiResource('stores', 'StoreController');
    require __DIR__.'/work-orders.php';
    Route::apiResource('major_accounts', 'MajorAccountController');
    Route::apiResource('cost_centers', 'CostCenterController');
    Route::apiResource('contacts', 'ContactController');
    Route::apiResource('stores', 'StoreController');
    Route::apiResource('vendors', 'VendorController');
    Route::apiResource('maintenances', 'MaintenanceController');
});
