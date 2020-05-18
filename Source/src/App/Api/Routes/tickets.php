<?php

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
