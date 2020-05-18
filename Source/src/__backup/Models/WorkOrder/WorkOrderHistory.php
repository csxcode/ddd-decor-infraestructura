<?php namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Model;

class WorkOrderHistory extends Model {
    protected $table = 'work_order_history';
    protected $guarded = [];
    public $timestamps = false;
}
