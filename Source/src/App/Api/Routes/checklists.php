<?php

Route::group(['namespace' => 'CheckList'], function() {
    Route::prefix('checklist')->group(function(){

        //-----------------------------------
        // checklist_status
        //-----------------------------------
        Route::get('/status', 'ChecklistStatusController@All');

        //-----------------------------------
        // checklist_item_type
        //-----------------------------------
        Route::get('/item_types', 'ChecklistItemTypeController@All');

        //-----------------------------------
        // checklist
        //-----------------------------------
        Route::post('/', 'ChecklistController@Create');
        Route::patch('/{id}', 'ChecklistController@Update');
        Route::get('/{id}', 'ChecklistController@Get');
        Route::get('/', 'ChecklistController@All');

        //-----------------------------------
        // checklist_item_details
        //-----------------------------------
        Route::post('/{id}/items', 'ChecklistItemDetailsController@Create');
        Route::patch('/{id}/items', 'ChecklistItemDetailsController@Update');
        Route::get('/{id}/items', 'ChecklistItemDetailsController@Get');

        // (update photos fields) to checklist_item_details
        Route::post('/{id}/item/{item_id}/photos', 'ChecklistItemDetailsController@PhotoProcess');

        // (update video field) to checklist_item_details
        Route::post('/{id}/item/{item_id}/video', 'ChecklistItemDetailsController@VideoProcess');
    });
});
