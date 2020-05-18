<?php namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Model;

class WorkOrderContact extends Model {

    protected $table = 'work_order_contact';
    protected $guarded = ['id'];
    public $timestamps = false;   
                
}