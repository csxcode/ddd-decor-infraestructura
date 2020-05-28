<?php

namespace App\UseCases\Ticket;

use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Helpers\FileHelper;
use App\Models\Ticket\Ticket;
use App\Services\MailService;
use FunctionHelper;
use Illuminate\Support\Facades\File;

class VideoPostProcess
{
    public function execute($data, $ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->first();

        $path = \Config::get('app.path_ticket_photos') . $ticket->id . '/';
        \FunctionHelper::createFolder($path);

        $files_to_delete = [];
        $message = null;

        foreach ($data as $item) {

            if ($item['action'] == ActionEnum::DELETE) {

                $message = $this->addGlobalArrayByTypeToDelete($item, $path, $ticket, $files_to_delete);
                $this->updateTicket($ticket);
            } else {

                $guid = FunctionHelper::CreateGUID(16);
                $message = $this->addGlobalArrayByTypeToDelete($item, $path, $ticket, $files_to_delete);
                $this->updateTicket($ticket, $guid, $item['video']->getClientOriginalName());
                $this->saveVideo($item, $guid, $path);
            }
        }

        $this->deleteMassiveFiles($files_to_delete);

        return $message;
    }

    private function addGlobalArrayByTypeToDelete($item, $path, $ticket, &$files_to_delete)
    {
        $message = null;

        if ($item['action'] == ActionEnum::EDIT) {

            $path_file_to_delete = $path . $ticket['video_guid'] . '.' . FileHelper::GetExtensionFromFilename($ticket['video_name']);
            array_push($files_to_delete, $path_file_to_delete);
            $message = 'video was updated successfully';
        } else if ($item['action'] == ActionEnum::DELETE) {

            $path_file_to_delete = $path . $ticket['video_guid'] . '.' . FileHelper::GetExtensionFromFilename($ticket['video_name']);
            array_push($files_to_delete, $path_file_to_delete);
            $message = 'video was delete successfully';
        } else {
            $message = 'video was added successfully';
        }

        return $message;
    }

    private function saveVideo($item, $guid, $path)
    {
        $extension = $item['video']->getClientOriginalExtension();
        $safeName = $guid . '.' . $extension;

        $item['video']->move($path, $safeName);
    }

    private function updateTicket(&$ticket, $guid = null, $name = null)
    {
        $ticket->update(
            [
                'video_guid' => $guid,
                'video_name' => $name
            ]
        );
    }

    private function deleteMassiveFiles($files_to_delete)
    {
        try {

            foreach ($files_to_delete as $path) {
                if (file_exists($path)) {
                    File::delete($path);
                }
            }
        } catch (\Exception $e) {
            MailService::SendErrorMail($e, AccessTypeEnum::Api);
        }
    }
}
