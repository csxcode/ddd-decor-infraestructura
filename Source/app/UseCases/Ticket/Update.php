<?php

namespace App\UseCases\Ticket;

use App\Helpers\StringHelper;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketComment;
use App\Models\Ticket\TicketStatus;
use App\Models\User;
use Carbon\Carbon;

class Update
{
    public function execute($changes, $ticket, $user)
    {
        if ($changes) {
            $oldTicket = Ticket::find($ticket->id);

            $ticket->updated_by_user = User::GetCreatedByUser($user);
            $ticket->updated_at = Carbon::now();
            $ticket->save();
            $this->createTicketCommentIfStatusHasChanged($changes, $oldTicket, $user);

            (new RunProccessWhenStatusChanged())->execute($oldTicket, $ticket, $user);
        }

        return $ticket;
    }

    private function createTicketCommentIfStatusHasChanged($changes, $oldTicket, $user)
    {        
        if (array_key_exists('status_id', $changes)) 
        {            
            $ticketStatus = TicketStatus::find($oldTicket->status_id);
            $ticket_status_changed = TicketStatus::find($changes['status_id']);
            $ticket_comment_description_line_1 = 'Estado del ticket cambió de ' . $ticketStatus->name . ' a ' . $ticket_status_changed->name;
            $ticket_comment_description_line_2 = null;

            if (array_key_exists('log_status_reason', $changes)) {
                if (!StringHelper::IsNullOrEmptyString($changes['log_status_reason'])) {
                    $ticket_comment_description_line_2 = "\r\nMotivo: " . $changes['log_status_reason'];
                }
            }

            $ticket_comment_description = $ticket_comment_description_line_1 . $ticket_comment_description_line_2;

            TicketComment::create([
                'ticket_id' => $oldTicket->id,
                'description' => $ticket_comment_description,
                'created_at' => Carbon::now(),
                'created_by_user' => User::GetCreatedByUser($user)
            ]);
        }
    }
}
