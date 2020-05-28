<?php

namespace App\UseCases\Ticket;

use App\Enums\ActionFileEnum;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketPhoto;
use App\Models\Views\TicketSearch;
use App\Models\WorkOrder\WorkOrder;
use App\UseCases\WorkOrder\Create as WorkOrderCreate;
use App\UseCases\WorkOrder\PhotoPostProcess as WorkOrderPhotoPostProcess;

class CreateWorkOrderByTicket
{
    private $workOrderCreateAction;
    private $workOrderPhotoCreateAction;

    public function __construct()
    {
        $this->workOrderCreateAction = new WorkOrderCreate();
        $this->workOrderPhotoCreateAction = new WorkOrderPhotoPostProcess();
    }


    public function execute(Ticket $ticket, $user): WorkOrder
    {
        $workOrder = $this->generateWorkOrder($ticket, $user);
        $this->generateWorkOrderPhotos($workOrder->id, $ticket->id);
        $this->copyVideo($ticket, $workOrder);

        return $workOrder;
    }

    private function generateWorkOrder(Ticket $ticket, $user): WorkOrder
    {
        $ticketViewData = TicketSearch::find($ticket->id);

        $work_specs = 'Prioridad: ' . $ticketViewData->priority_name . PHP_EOL .
            'Ubicación: ' . $ticketViewData->location . PHP_EOL .
            'Descripción: ' . $ticketViewData->description;

        $workOrder = [
            'work_specs' => $work_specs,
            'branch_location_id' => $ticket->branch_location_id,
            'video_guid' => \FunctionHelper::CreateGUID(16),
            'video_name' => $ticket->video_name,
            'ticket_id' => $ticket->id
        ];

        return $this->workOrderCreateAction->execute($workOrder, $user);
    }

    private function copyVideo(Ticket $ticket, WorkOrder $workOrder)
    {
        if (StringHelper::IsNullOrEmptyString($ticket->video_guid) || StringHelper::IsNullOrEmptyString($ticket->video_name))
            return;

        if (StringHelper::IsNullOrEmptyString($workOrder->video_guid) || StringHelper::IsNullOrEmptyString($workOrder->video_name))
            return;

        $fromData = [
            'module_path' => \Config::get('app.path_ticket_photos'),
            'id' => $ticket->id,
            'guid' => $ticket->video_guid,
            'name' => $ticket->video_name
        ];

        $toData = [
            'module_path' => \Config::get('app.path_wo_files'),
            'id' => $workOrder->id,
            'guid' => $workOrder->video_guid,
            'name' => $workOrder->video_name
        ];

        FileHelper::copyFileByGuid($fromData, $toData);
    }

    private function generateWorkOrderPhotos(int $workOrderId, int $ticketId): void
    {
        $ticketPhotos = TicketPhoto::where('ticket_id', $ticketId)->get();
        $photosData = [];

        foreach ($ticketPhotos as $ticketPhoto) {

            $filePath = FileHelper::getFileNamePathByGuid(\Config::get('app.path_ticket_photos'), $ticketId, $ticketPhoto->guid, $ticketPhoto->name);

            if (!file_exists($filePath))
                break;

            $photo = [
                'action' => ActionFileEnum::CREATE,
                'order' => $ticketPhoto->order,
                'guid' => \FunctionHelper::CreateGUID(16),
                'name' => $ticketPhoto->name,
                'base64' => Base64Helper::convert($filePath),
            ];
            array_push($photosData, $photo);
        }

        $this->workOrderPhotoCreateAction->execute($photosData, $workOrderId);
    }
}
