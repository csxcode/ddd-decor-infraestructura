<?php

// ======================================================================
// ==================== Public Endpoints ================================
// ======================================================================


// ------------------------------------------
// [Utils]
// ------------------------------------------
Route::get('/clear', 'GlobalController@clear');

// ------------------------------------------
// [Authentication]
// ------------------------------------------
Route::prefix('auth')->group(function(){
    Route::get('login', 'Auth\LoginController@show');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout');
});

// ------------------------------------------
// Public: [Photos, Videos, Files]
// ------------------------------------------

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


// ======================================================================
// ==================== Protected Endpoints =============================
// ======================================================================
Route::group(['middleware' => 'auth.web'], function () {

    // Dashboard (Home Page)
    Route::get('/', 'DashboardController@index');
    Route::get('/home', 'DashboardController@index')->name('homex');

    // ------------------------------------------
    // [Global]
    // ------------------------------------------
    Route::get('global/ajax/get_branches_by_store', array('as' => 'global.ajax.get_branches_by_store', 'uses' => 'GlobalController@GetBranchesByStore'));


    // ------------------------------------------
    // [Dashboard]
    // ------------------------------------------
    Route::get('dashboard', array('as' => 'dashboard', 'uses' => 'DashboardController@index'));

    // ------------------------------------------
    // [Checklist]
    // ------------------------------------------
    Route::resource('checklist', 'ChecklistController');
    Route::get('checklist_export', array('as' => 'checklist.export', 'uses' => 'ChecklistController@Export'));
    Route::get('checklist/{id}/photos/{guid}/download', array('as' => 'checklist.photos.download', 'uses' => 'ChecklistController@DownloadPhoto'));
    Route::get('checklist/{id}/videos/{guid}/download', array('as' => 'checklist.videos.download', 'uses' => 'ChecklistController@DownloadVideo'));
    Route::post('checklist/ajax/update_status', array('as' => 'checklist.ajax.update_status', 'uses' => 'ChecklistController@UpdateStatus'));


    // ------------------------------------------
    // [Structure Checklist]
    // ------------------------------------------
    Route::prefix('tables')->group(function() {
        Route::resource('checklist_structure', 'ChecklistStructureController');
        Route::get('checklist_structure_grid_partial', array('as' => 'checklist_structure.grid.partial', 'uses' => 'ChecklistStructureController@GetGridViewData'));
        Route::get('checklist_structure_export', array('as' => 'checklist_structure.export', 'uses' => 'ChecklistStructureController@Export'));

        // Types
        Route::get('checklist_structure/types/{id}/{action}', array('as' => 'checklist_structure.type.show', 'uses' => 'ChecklistStructureController@ShowTypeDataEntry'));
        Route::post('checklist_structure/types/{id}', array('as' => 'checklist_structure.type.save', 'uses' => 'ChecklistStructureController@SaveTypeDataEntry'));
        Route::delete('checklist_structure/types/{id}', array('as' => 'checklist_structure.type.delete', 'uses' => 'ChecklistStructureController@DeleteType'));

        // Subtypes
        Route::get('checklist_structure/subtypes/{id}/{type_id}/{action}', array('as' => 'checklist_structure.subtype.show', 'uses' => 'ChecklistStructureController@ShowSubtypeDataEntry'));
        Route::post('checklist_structure/subtypes/{id}', array('as' => 'checklist_structure.subtype.save', 'uses' => 'ChecklistStructureController@SaveSubtypeDataEntry'));
        Route::delete('checklist_structure/subtypes/{id}', array('as' => 'checklist_structure.subtype.delete', 'uses' => 'ChecklistStructureController@DeleteSubtype'));

        // Items
        Route::get('checklist_structure/items/{id}/{subtype_id}/{action}', array('as' => 'checklist_structure.item.show', 'uses' => 'ChecklistStructureController@ShowItemDataEntry'));
        Route::post('checklist_structure/items/{id}', array('as' => 'checklist_structure.item.save', 'uses' => 'ChecklistStructureController@SaveItemDataEntry'));
        Route::delete('checklist_structure/items/{id}', array('as' => 'checklist_structure.item.delete', 'uses' => 'ChecklistStructureController@DeleteItem'));
    });

    // ------------------------------------------
    // [Tickets]
    // ------------------------------------------
    Route::resource('tickets', 'TicketController');
    Route::get('tickets_export', array('as' => 'tickets.export', 'uses' => 'TicketController@Export'));
    Route::get('ticket/{id}/photos/{guid}/download', array('as' => 'tickets.photos.download', 'uses' => 'TicketController@DownloadPhoto'));
    Route::post('stores/ajax/save_data/{ticket_id}', array('as' => 'tickets.ajax.save_data', 'uses' => 'TicketController@SaveData'));

    // ------------------------------------------
    // [Users]
    // ------------------------------------------
    Route::resource('users', 'UserController');

    // ------------------------------------------
    // [Sessions]
    // ------------------------------------------
    Route::resource('sessions', 'SessionController');
    Route::patch('sessions/eject/{id}', array('as' => 'sessions.eject', 'uses' => 'SessionController@eject'));
});


