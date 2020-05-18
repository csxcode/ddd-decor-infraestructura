<?php

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
