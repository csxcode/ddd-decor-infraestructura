<?php
namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Model;

class WorkOrderQuoteStatus extends Model {

    protected $table = 'quote_status';

    const STATUS_PENDIENTE = 1;
    const STATUS_COTIZADO = 2;
    const STATUS_ACEPTADO = 3;
    const STATUS_DENEGADO = 4;

}
