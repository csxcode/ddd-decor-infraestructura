<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/27/2019
 * Time: 3:52 PM
 */

namespace App\Models\Checklist;


use Illuminate\Database\Eloquent\Model;

class ChecklistItemDetail extends Model
{
    protected $table = 'checklist_item_details';
    public $timestamps = false;
    protected $guarded = array();

}