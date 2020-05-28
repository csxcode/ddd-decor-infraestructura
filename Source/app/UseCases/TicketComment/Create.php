<?php

namespace App\UseCases\TicketComment;

use App\Models\Ticket\TicketComment;
use App\Models\User;
use Carbon\Carbon;

class Create
{
    public function execute($ticketId, $save, $user)
    {
        return TicketComment::create([
            'ticket_id' => $ticketId,
            'description' => $save['description'],
            'created_at' => Carbon::now(),
            'created_by_user' => User::GetCreatedByUser($user)
        ]);
    }
}
