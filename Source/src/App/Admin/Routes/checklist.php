<?php

Route::resource('checklist', 'ChecklistController');
Route::get('checklist_export', array('as' => 'checklist.export', 'uses' => 'ChecklistController@Export'));
Route::get('checklist/{id}/photos/{guid}/download', array('as' => 'checklist.photos.download', 'uses' => 'ChecklistController@DownloadPhoto'));
Route::get('checklist/{id}/videos/{guid}/download', array('as' => 'checklist.videos.download', 'uses' => 'ChecklistController@DownloadVideo'));
Route::post('checklist/ajax/update_status', array('as' => 'checklist.ajax.update_status', 'uses' => 'ChecklistController@UpdateStatus'));
