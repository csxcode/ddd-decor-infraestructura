<?php

// Public
require __DIR__.'/public.php';

// Protected
Route::group(['middleware' => 'auth.web'], function () {
    require __DIR__.'/dashboard.php';
    require __DIR__.'/checklist.php';
    require __DIR__.'/tables.php';
    require __DIR__.'/tickets.php';
    Route::resource('users', 'UserController');
    require __DIR__.'/sessions.php';
});


