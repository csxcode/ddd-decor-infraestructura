<?php

namespace App\UseCases\Ticket;

use App\Models\Ticket\TicketPhoto;
use App\Models\Views\TicketSearch;

class Show
{
    public function execute(int $ticketId)
    {
        $data = TicketSearch::find($ticketId);
        $data->photos = $this->getPhotos($ticketId);

        return $data;
    }

    private function getPhotos(int $ticketId)
    {
        $photos = TicketPhoto::select('id', 'order', 'guid', 'name', 'ticket_id')
            ->where('ticket_id', $ticketId)
            ->orderBy('order')
            ->get();

        $photos->makeHidden('ticket_id');
        $photos->makeHidden('id');

        return $photos;
    }
}
