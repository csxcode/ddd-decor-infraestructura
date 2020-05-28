<?php

namespace App\UseCases\TicketComment;

use App\Models\Ticket\TicketComment;
use Illuminate\Support\Facades\DB;

class Search
{
    public function execute($ticketId)
    {
        return TicketComment::select(DB::raw(
            'id, 
            description, 
            UNIX_TIMESTAMP(CONVERT_TZ(created_at, \'+00:00\', @@global.time_zone)) as created_atx,
            created_by_user'
        ))
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
