<?php

namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\StringHelper;
use Illuminate\Http\Response;

class TicketVideoValidation extends BaseValidation
{
    public function store($request, $resource, $user, $ticket, &$data)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $video1 = $request->file('video');
        $data = [];

        // --------------------------------------------
        // Validations
        // --------------------------------------------

        // [Ticket]
        if (!$ticket) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'Ticket']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        $ticketvalidation = new TicketValidation();

        // By Role        
        $error_response = $ticketvalidation->checkRoleIsAllowed(AppObjectNameEnum::TICKET, $user, ActionEnum::EDIT, $ticket);

        if ($error_response)
            return $error_response;

        // Check Ticket
        $error_response = $ticketvalidation->checkIfTicketCanBeUpdated(AppObjectNameEnum::TICKET, $ticket, 'status_id');

        if ($error_response)
            return $error_response;

        // set data to array called "data"
        $action = null;
        if ($video1 == null) {
            $action = ActionEnum::DELETE;
        } else {
            if (StringHelper::IsNullOrEmptyString($ticket->video_guid)) {
                $action = ActionEnum::CREATE;
            } else {
                $action = ActionEnum::EDIT;
            }
        }

        array_push($data, [
            'action' => $action,
            'video' => $video1
        ]);

        // Video
        $max_size = env('APP_MAX_SIZE_VIDEO_UPLOAD');

        foreach ($data as $item) {

            $video = $item['video'];

            if ($video != null) {

                // Check video size
                if ($video->getSize() > $max_size) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.limit', ['attribute' => $max_size . ' bytes']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'video'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Check video extensions
                $error_response = $this->checkVideoExtensionIsAllowed($resource, $video, 'video');

                if ($error_response)
                    return $error_response;
            }
        }

        return null;
    }
}
