<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api'], function()
{
    // ======================================================================
    // ==================== Public Endpoints ================================
    // ======================================================================

    // [Authentication]
    Route::prefix('auth')->group(function(){
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::get('ping', 'AuthController@ping');
    });


    // ======================================================================
    // ==================== Protected Endpoints =============================
    // ======================================================================

    Route::group(['middleware' => 'auth.api'], function() {

        // [Dashboard]
        Route::prefix('dashboard')->group(function(){
            Route::get('/', 'DashboardController@Get');
        });

        // [Checklist]
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

        // [Ticket]
        Route::group(['namespace' => 'Ticket'], function() {
            Route::prefix('tickets')->group(function () {

                Route::get('/status', 'TicketStatusController@All');
                Route::get('/types', 'TicketTypeController@All');

                Route::get('/', 'TicketController@All');
                Route::get('/{id}', 'TicketController@Get');
                Route::post('/', 'TicketController@Create');
                Route::patch('/{id}', 'TicketController@Update');
                Route::post('/{id}/photos', 'TicketPhotoController@AddPhotos');

                // update video fields of ticket table
                Route::post('/{id}/video', 'TicketController@VideoProcess');


                //-----------------------------------
                // ticket_comment
                //-----------------------------------
                Route::post('/{id}/comments', 'TicketCommentController@Create');
                Route::get('/{id}/comments', 'TicketCommentController@Get');
            });
        });

        // [Stores]
        Route::prefix('stores')->group(function(){
            Route::get('/', 'StoreController@All');
        });

        // [WorkOrder]
        Route::group(['namespace' => 'WorkOrder'], function() {

            // work_orders
            Route::apiResource('work_orders', 'WorkOrderController');

            // work_orders > photos
            Route::post('/work_orders/{woId}/photos', 'WorkOrderPhotoController@store');
            Route::get('/work_orders/{woId}/photos', 'WorkOrderPhotoController@index');

            // work_orders > files
            Route::post('/work_orders/{woId}/files', 'WorkOrderFileController@store');
            Route::get('/work_orders/{woId}/files', 'WorkOrderFileController@index');
            Route::delete('/work_orders/{woId}/files', 'WorkOrderFileController@destroy');

            // work_orders > videos
            Route::post('/work_orders/{woId}/videos', 'WorkOrderVideoController@store');
            Route::delete('/work_orders/{woId}/videos', 'WorkOrderVideoController@destroy');

            // work_orders > contacts
            Route::apiResource('work_orders.contacts', 'WorkOrderContactController');

            // work_orders > cost_centers
            Route::apiResource('work_orders.cost_centers', 'WorkOrderCostCenterController');

            // work_orders > histories
            Route::apiResource('work_orders.histories', 'WorkOrderHistoryController');

            // work_orders > histories > files
            Route::post('/work_orders/{woId}/histories/{wohId}/files', 'WorkOrderHistoryFileController@store');

            // work_orders > histories > photos
            Route::post('/work_orders/{woId}/histories/{wohId}/photos', 'WorkOrderHistoryPhotoController@store');

            // work_orders > histories > videos
            Route::post('/work_orders/{woId}/histories/{wohId}/videos', 'WorkOrderHistoryVideoController@store');

            // work_orders > quotes
            Route::apiResource('work_orders.quotes', 'WorkOrderQuoteController');

            // work_orders > quotes > files
            Route::post('/work_orders/{woId}/quotes/{woqId}/files', 'WorkOrderQuoteFileController@store');
            Route::delete('/work_orders/{woId}/quotes/{woqId}/files', 'WorkOrderQuoteFileController@destroy');

            // work_orders > quotes > photos
            Route::post('/work_orders/{woId}/quotes/{woqId}/photos', 'WorkOrderQuotePhotoController@store');
            Route::delete('/work_orders/{woId}/quotes/{woqId}/photos', 'WorkOrderQuotePhotoController@destroy');
        });

        // [MajorAccount]
        Route::apiResource('major_accounts', 'MajorAccountController');

        // [CostCenter]
        Route::apiResource('cost_centers', 'CostCenterController');

        // [Contact]
        Route::apiResource('contacts', 'ContactController');

        // [Store]
        Route::apiResource('stores', 'StoreController');

        // [Vendor]
        Route::apiResource('vendors', 'VendorController');

        // [Maintenance]
        Route::apiResource('maintenances', 'MaintenanceController');

    });

});
