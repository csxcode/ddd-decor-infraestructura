<?php

namespace App\UseCases\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\User;
use App\UseCases\Ticket\NotifyEmail\NotifyEmailByNewStatus;
use Carbon\Carbon;

class Create
{
    public function execute($save, $user)
    {
        $ticket = Ticket::create([
            'ticket_number' => Ticket::GenerateNumber(),
            'status_id' => $save['status_id'],
            'priority_id' => $save['priority_id'],
            'type_id' => $save['type_id'],
            'branch_location_id' => $save['branch_location_id'],
            'description' => $save['description'],
            'status_reason' => $save['status_reason'],
            'delivery_date' => $save['delivery_date'],
            'subtype_id' => $save['subtype_id'],
            'location' => $save['location'],
            'reference_doc' => $save['reference_doc'],
            'created_at' => Carbon::now(),
            'created_by_user' => User::GetCreatedByUser($user)
        ]);

        (new NotifyEmailByNewStatus())->execute($ticket->id, $user);

        return $ticket;
    }
}
