<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 */
class WorkOrder extends Model
{
    protected $table = 'work_order';
    protected $guarded = ['id'];
}
