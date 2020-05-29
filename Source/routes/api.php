<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api'], function () {
  
    // Public
    Route::prefix('auth')->group(function () {
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::get('ping', 'AuthController@ping');
    });

    // Protected
    Route::group(['middleware' => 'auth.api'], function () {

        Route::group(['namespace' => 'Ticket'], function () {
            $module = 'tickets';
            Route::resource($module . '/status', 'TicketStatusController');
            Route::resource($module . '/types', 'TicketTypeController');
            Route::resource($module, 'TicketController');
            Route::resource($module . '/{id}/photos', 'TicketPhotoController');
            Route::resource($module . '/{id}/video', 'TicketVideoController');
            Route::resource($module . '/{id}/comments', 'TicketCommentController');
        });

    });

});
