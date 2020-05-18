<?php
namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStatus extends Model
{
    protected $table = 'work_order_status';

    const STATUS_COTIZANDO = 1;
    const STATUS_PENDIENTE_INICIAR = 2;
    const STATUS_INICIADO = 3;
    const STATUS_PAUSADO = 4;
    const STATUS_TERMINADO = 5;
    const STATUS_CONFIRMADO = 6;
    const STATUS_CERRADO = 7;
    const STATUS_ANULADO = 8;
    const STATUS_REAPERTURADO = 9;    
}