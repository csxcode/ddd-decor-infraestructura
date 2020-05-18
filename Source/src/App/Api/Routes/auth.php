<?php

Route::prefix('auth')->group(function(){
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::get('ping', 'AuthController@ping');
});
