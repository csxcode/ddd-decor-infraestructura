<?php

Route::get('/', 'DashboardController@index');
Route::get('/home', 'DashboardController@index');
Route::get('global/ajax/get_branches_by_store', array('as' => 'global.ajax.get_branches_by_store', 'uses' => 'GlobalController@GetBranchesByStore'));
Route::get('dashboard', array('as' => 'dashboard', 'uses' => 'DashboardController@index'));
