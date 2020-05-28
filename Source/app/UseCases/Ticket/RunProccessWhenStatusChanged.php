<?php

namespace App\UseCases\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketStatus;
use App\UseCases\Ticket\NotifyEmail\NotifyEmailByAnnulledStatus;
use App\UseCases\Ticket\NotifyEmail\NotifyEmailByConfirmedStatus;

class RunProccessWhenStatusChanged
{
    public function execute(Ticket $oldTicket, Ticket $ticket, $user)
    {
        if ($this->checkStatusHasChangedFromNewToConfirmed($oldTicket, $ticket)) {
            $createWorkOrderByTicketAction = new CreateWorkOrderByTicket();
            $workOrder = $createWorkOrderByTicketAction->execute($ticket, $user);
            (new NotifyEmailByConfirmedStatus())->execute($ticket->id, $workOrder);
        }

        if ($this->checkStatusHasChangedFromNewToAnnulled($oldTicket, $ticket)) {
            (new NotifyEmailByAnnulledStatus())->execute($ticket->id);
        }
    }

    private function checkStatusHasChangedFromNewToConfirmed(Ticket $oldTicket, Ticket $ticket)
    {
        $keyName = 'status_id';
        $oldStatusIsNew = $oldTicket->$keyName == TicketStatus::STATUS_NUEVO;
        $newStatusIsConfirmed = $ticket->$keyName == TicketStatus::STATUS_CONFIRMADO;

        if (!($oldStatusIsNew && $newStatusIsConfirmed))
            return false;

        return true;
    }

    private function checkStatusHasChangedFromNewToAnnulled(Ticket $oldTicket, Ticket $ticket)
    {
        $keyName = 'status_id';
        $oldStatusIsNew = $oldTicket->$keyName == TicketStatus::STATUS_NUEVO;
        $newStatusIsAnnulled = $ticket->$keyName == TicketStatus::STATUS_ANULADO;

        if (!($oldStatusIsNew && $newStatusIsAnnulled))
            return false;

        return true;
    }
}
