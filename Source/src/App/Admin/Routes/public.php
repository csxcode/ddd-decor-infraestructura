<?php

// --------------------------------------------------
// Utils
// --------------------------------------------------
Route::get('/clear', 'GlobalController@clear');

// --------------------------------------------------
// Authentication
// --------------------------------------------------
Route::prefix('auth')->group(function(){
    Route::get('login', 'Auth\LoginController@show');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout');
});

// --------------------------------------------------
// Resources [Photos, Videos, Files]
// --------------------------------------------------
$webUrl = Config::get('app.web_url');
$webPhotoPath = Config::get('app.web_photo_path');
$webFilePath = Config::get('app.web_file_path');
$webVideoPath = Config::get('app.web_video_path');

# [WorkOrder]
Route::get(str_replace ('{module}', 'work_orders', $webPhotoPath), 'WorkOrderPublicController@photo');
Route::get(str_replace ('{module}', 'work_orders', $webVideoPath), 'WorkOrderPublicController@video');
Route::get(str_replace ('{module}', 'work_orders', $webFilePath), 'WorkOrderPublicController@file');

# [WorkOrderQuote]
Route::get(str_replace ('{module}', 'work_order_quotes', $webPhotoPath), 'WorkOrderQuotePublicController@photo');
Route::get(str_replace ('{module}', 'work_order_quotes', $webFilePath), 'WorkOrderQuotePublicController@file');

# [WorkOrderHistory]
Route::get(str_replace ('{module}', 'work_order_histories', $webPhotoPath), 'WorkOrderHistoryPublicController@photo');
Route::get(str_replace ('{module}', 'work_order_histories', $webVideoPath), 'WorkOrderHistoryPublicController@video');
Route::get(str_replace ('{module}', 'work_order_histories', $webFilePath), 'WorkOrderHistoryPublicController@file');

