<?php
namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class TicketTypeSub extends Model
{
    protected $table = 'ticket_type_sub';
    protected $hidden = ['ticket_type_id'];

}