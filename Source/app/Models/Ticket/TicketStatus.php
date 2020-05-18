<?php
namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    protected $table = 'ticket_status';
    
    const STATUS_NUEVO = 1;
    const STATUS_CONFIRMADO = 2;
    const STATUS_COMPLETADO = 3;
    const STATUS_ANULADO = 4;
    const STATUS_COTIZANDO = 6;
    const STATUS_EJECUTANDO = 7;            
}