<?php
namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{    
    protected $table = 'ticket_comment';
    protected $guarded = ['id'];
}