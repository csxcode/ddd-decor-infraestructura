<?php

namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Helpers\UserHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\UserStoreBranch;
use Illuminate\Http\Response;

class TicketCommentValidation
{
    public function store($request, $resource, $user, $ticket, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------                     
        $description = ($request->exists('description') ? StringHelper::Trim($request->get('description')) : null);

        $save = [
            'description' => null,
        ];

        // --------------------------- Ticket ---------------------------
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


        // ------------- Description ---------------------
        if ($description == null) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'description']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'description'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $save['description'] = $description;

        return null;
    }

    public function index($resource, $user, $ticket)
    {
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

        return null;
    }
}
