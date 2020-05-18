<?php

Route::prefix('tables')->group(function() {

    // ----------------------------------------------------
    // [Structure Checklist]
    // ----------------------------------------------------
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
