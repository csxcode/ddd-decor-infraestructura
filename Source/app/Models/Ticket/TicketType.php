<?php
namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{

    const TICKET_TYPE_MANTENIMIENTO = 1;
    const TICKET_TYPE_REPOSICION = 2;
    const TICKET_TYPE_RECOJO = 3;
    const TICKET_TYPE_CAMBIAR = 4;
    const TICKET_TYPE_NUEVA_EXHIBICION = 5;


    protected $table = 'ticket_type';

    public function sub_types() {
        return $this->hasMany(TicketTypeSub::class, 'ticket_type_id', 'id')->orderBy('name');
    }

}