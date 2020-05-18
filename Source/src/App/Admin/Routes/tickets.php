<?php

Route::resource('tickets', 'TicketController');
Route::get('tickets_export', array('as' => 'tickets.export', 'uses' => 'TicketController@Export'));
Route::get('ticket/{id}/photos/{guid}/download', array('as' => 'tickets.photos.download', 'uses' => 'TicketController@DownloadPhoto'));
Route::post('stores/ajax/save_data/{ticket_id}', array('as' => 'tickets.ajax.save_data', 'uses' => 'TicketController@SaveData'));
