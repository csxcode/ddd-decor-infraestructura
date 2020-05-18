<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 5/30/2019
 * Time: 4:23 PM
 */

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;

class TicketPhoto extends Model
{
    protected $table = 'ticket_photo';
    protected $guarded = ['id'];
    public $timestamps = false;
}