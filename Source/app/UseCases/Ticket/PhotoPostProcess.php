<?php

namespace App\UseCases\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketPhoto;

class PhotoPostProcess
{
    public function execute($photos_data, $ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->first();

        $path = \Config::get('app.path_ticket_photos') . $ticket->id . '/';
        \FunctionHelper::createFolder($path);

        $exclude_guids = [];

        foreach ($photos_data['photos'] as $photo) {

            if ($photo['new']) {
                $this->createPhoto($ticket->id, $photo);
                $this->savePhoto($photo, $path);
            }

            array_push($exclude_guids, $photo['guid']);
        }

        $this->deletePhotosRecords($ticket->id, $exclude_guids);
    }

    private function deletePhotosRecords($ticketId, $exclude_guids)
    {
        TicketPhoto::where('ticket_id', $ticketId)
            ->whereNotIn('guid', $exclude_guids)
            ->forceDelete();
    }

    private function createPhoto($ticketId, $photo): void
    {
        TicketPhoto::create([
            'ticket_id' => $ticketId,
            'guid' => $photo['guid'],
            'name' => $photo['name'],
            'order' => $photo['order'],
        ]);
    }

    private function savePhoto($photo, $path)
    {
        $safeName = $photo['guid'] . '.' . $photo['extension'];
        file_put_contents($path . $safeName, base64_decode($photo['photo']));
    }
}
