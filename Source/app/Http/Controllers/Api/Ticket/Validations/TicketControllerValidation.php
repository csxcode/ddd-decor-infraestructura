<?php

namespace App\Http\Controllers\Api\Ticket\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\FileHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\Base64Helper;
use App\Helpers\StringHelper;
use App\Helpers\UserHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Branch;
use App\Models\Priority;
use App\Models\Ticket\TicketPhoto;
use App\Models\Ticket\TicketStatus;
use App\Models\Ticket\TicketType;
use App\Models\Ticket\TicketTypeSub;
use App\Models\User;
use App\Models\UserStoreBranch;
use Carbon\Carbon;
use Illuminate\Http\Response;

class TicketControllerValidation
{

    public static $video_ext = ['mp4', 'mpeg'];

    public static function CreateValidation($request, $resource, $user, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $priority_id = StringHelper::Trim($request->get('priority_id'));
        $type_id = StringHelper::Trim($request->get('type_id'));
        $branch_id = StringHelper::Trim($request->get('branch_id'));
        $description = StringHelper::Trim($request->get('description'));
        $delivery_date = StringHelper::Trim($request->get('delivery_date'));
        $subtype_id = StringHelper::Trim($request->get('subtype_id'));
        $location = StringHelper::Trim($request->get('location'));

        $save = [
            'priority_id' => null,
            'status_id' => TicketStatus::TICKET_STATUS_NEW,
            'status_reason' => null,
            'type_id' => null,
            'branch_id' => null,
            'description' => $description,
            'delivery_date' => null,
            'subtype_id' => null,
            'location' => null,
            'approved_at' => null,
            'approved_by_user' => null,
            'rejected_at' => null,
            'rejected_by_user' => null,
        ];

        $role = $user->role;

        // ------------- Priority ---------------------
        if (StringHelper::IsNullOrEmptyString($priority_id)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'priority']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'priority_id'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }else {
            if (!ctype_digit($priority_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'priority']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'priority_id',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $priority = Priority::find($priority_id);
        if(!$priority){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'priority']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'priority_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Set data to save
        $save['priority_id'] = $priority_id;

        // ------------- Type ---------------------
        if (StringHelper::IsNullOrEmptyString($type_id)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'tipo']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'type_id'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }else {
            if (!ctype_digit($type_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'tipo']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'type_id',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $ticket_type = TicketType::find($type_id);
        if(!$ticket_type){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'tipo']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'type_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Set data to save
        $save['type_id'] = $type_id;

        // ------------- Branch ID ---------------------
        if (StringHelper::IsNullOrEmptyString($branch_id)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'sucursal']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'branch_id'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }else {
            if (!ctype_digit($branch_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'sucursal']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_id',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $branch = Branch::find($branch_id);
        if(!$branch){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'sucursal']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'branch_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'sucursal']),
            $resource, 'branch_id');

        if ($error_response)
            return $error_response;


        // Set data to save
        $save['branch_id'] = $branch_id;


        // ------------- Delivery Date ---------------------
        if (!StringHelper::IsNullOrEmptyString($delivery_date)) {

            // Check: delivery_date
            try {
                $x = Carbon::createFromTimestamp($delivery_date);

                // Set data to save
                $save['delivery_date'] = $x;

            } catch (\Exception $exp) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'delivery_date']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'delivery_date'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // ------------- Sub Type ---------------------
        if (StringHelper::IsNullOrEmptyString($subtype_id)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'sub tipo']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'subtype_id'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }else {
            if (!ctype_digit($subtype_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'sub tipo']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'subtype_id',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $sub_type_data = TicketTypeSub::find($subtype_id);
        if(!$sub_type_data){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'sub tipo']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'subtype_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Set data to save
        $save['subtype_id'] = $subtype_id;

        // Location
        if($request->exists('location')){
            $save['location'] = $location;
        }

        return null;
    }

    public static function AllValidation($request, $resource)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $store_id = StringHelper::Trim($request->get('store_id'));
        $branch_id = StringHelper::Trim($request->get('branch_id'));
        $status_id = StringHelper::Trim($request->get('status_id'));
        $type_id = StringHelper::Trim($request->get('type_id'));
        $date_from = StringHelper::Trim($request->get('date_from'));
        $date_to = StringHelper::Trim($request->get('date_to'));
        $per_page = StringHelper::Trim($request->get('per_page'));
        $page = StringHelper::Trim($request->get('page'));

        // --------------------------------------------
        // Validations
        // --------------------------------------------


        // Check: store_id
        if (!StringHelper::IsNullOrEmptyString($store_id)) {

            if (!ctype_digit($store_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'store_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'store_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // Check: branch_id
        if (!StringHelper::IsNullOrEmptyString($branch_id)) {
            if (!ctype_digit($branch_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'branch_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // Check: status_id
        if (!StringHelper::IsNullOrEmptyString($status_id)) {
            if (!ctype_digit($status_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'status']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // Check: type_id
        if (!StringHelper::IsNullOrEmptyString($type_id)) {
            if (!ctype_digit($type_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'type_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'type_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // Check: date_from
        try {
            if (!StringHelper::IsNullOrEmptyString($date_from)) {
                $x = Carbon::createFromTimestamp($date_from);
            }
        } catch (\Exception $exp) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => 'date_from']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'date_from'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }


        // Check: date_to
        try {
            if (!StringHelper::IsNullOrEmptyString($date_to)) {
                $x = Carbon::createFromTimestamp($date_to);
            }
        } catch (\Exception $exp) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => 'date_to']),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'date_to'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }


        // Check: pagination
        if (!StringHelper::IsNullOrEmptyString($per_page)) {
            if (!ctype_digit($per_page)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'per_page']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'per_page'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!StringHelper::IsNullOrEmptyString($page)) {
            if (!ctype_digit($page)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'page']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'page'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return null;
    }

    public static function GetValidation($request, $resource, $user, $ticket)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $role = $user->role->name;

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

        $ticket_clone = clone $ticket;


        // [Check UserStoreBrand Permission ]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission(
            $user, $ticket->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'ticket']),
            $resource, 'id');

        if ($error_response)
            return $error_response;

        // ------------------- Rules for USER ---------------------------
        $pass = false;

        // Visual Usuario (2), cualquier ticket de cualquier sucursal
        if(!$pass){
            if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {
                $pass = true;
            }
        }

        // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        if(!$pass){

            if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $check = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $ticket_clone->branch_id)
                    ->first();

                if($check != null){
                    $pass = true;
                }
            }
        }

        // Cualquier tipo de usuario, solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        // y el ticket haya sido creado por el usuario
        if(!$pass){

            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $ticket_clone->branch_id)
                ->first();

            $username_from_ticket = strtolower(UserHelper::ExtractUserNameFromString($ticket_clone->created_by_user));
            $username_from_user = strtolower($user->username);

            if($check != null && $username_from_ticket == $username_from_user){
                $pass = true;
            }

        }

        if(!$pass){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public static function UpdateValidation($request, $resource, $user, &$ticket, &$changes)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $status_id = ($request->get('status_id') !== null ? StringHelper::Trim($request->get('status_id')) : null) ;
        $type_id = ($request->get('type_id') !== null ? StringHelper::Trim($request->get('type_id')) : null) ;
        $branch_id = ($request->get('branch_id') !== null ? StringHelper::Trim($request->get('branch_id')) : null) ;
        $description = ($request->get('description') !== null ? StringHelper::Trim($request->get('description')) : null) ;
        $status_reason = ($request->get('status_reason') !== null ? StringHelper::Trim($request->get('status_reason')) : null) ;
        $delivery_date = ($request->get('delivery_date') !== null ? StringHelper::Trim($request->get('delivery_date')) : null) ;
        $subtype_id = ($request->get('subtype_id') !== null ? StringHelper::Trim($request->get('subtype_id')) : null) ;
        $priority_id = StringHelper::Trim($request->get('priority_id'));
        $location = StringHelper::Trim($request->get('location'));
        $resolution_comment = StringHelper::Trim($request->get('resolution_comment'));

        $role = $user->role->name;
        $ticket_clone = null;


        // ------------- Ticket ---------------------
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
        } else {
            $ticket_clone = clone $ticket;
        }

        // No permitir actualizar si el estado actual del ticket es Cerrado o Cancelado
        if ($ticket->status_id == TicketStatus::TICKET_STATUS_CLOSED || $ticket->status_id == TicketStatus::TICKET_STATUS_CANCELED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket_cannot_edit_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }


        // ------------- Status ---------------------
        if($status_id != null) {

            if (StringHelper::IsNullOrEmptyString($status_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'estado']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if (!ctype_digit(StringHelper::Trim($status_id))) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'estado']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'status_id',
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $ticket_status = TicketStatus::find($status_id);
            if (!$ticket_status) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'estado']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status_id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }


            // ................... Rules ....................


            // A. Actualizar el estado de “Nuevo” a “En Proceso”
            // Este paso se define como “aprobar un ticket”

            if($ticket_clone->status_id == TicketStatus::TICKET_STATUS_NEW && $status_id == TicketStatus::TICKET_STATUS_PROCESS){

                // Solo los usuarios Responsable de Tienda (4) y Visual Usuario (2) pueden aprobar un ticket
                if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER) || strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {

                    // Check if there is a change and set data to save
                    if($ticket->status_id != $status_id){
                        $ticket->status_id = $status_id;
                        $changes['status_id'] = $status_id;

                        $ticket->approved_at = Carbon::now();
                        $ticket->approved_by_user = User::GetCreatedByUser($user);

                        $changes['log_status_reason'] = null;
                    }
                } else{

                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => "Solo los usuarios Responsable de Tienda y Visual Usuario pueden aprobar un ticket",
                        'error_code' => ErrorCodesEnum::forbidden,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'status_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);

                }
            }



            // B. Anular un ticket

            if($status_id == TicketStatus::TICKET_STATUS_CANCELED){

                // Solo el usuario Visual Usuario (2) puede anular un ticket
                if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {

                    // Un ticket puede ser “Anulado” (5) solo si su estado actual es “Nuevo” (1) o “En Proceso” (2)
                    if($ticket_clone->status_id == TicketStatus::TICKET_STATUS_NEW || $ticket_clone->status_id == TicketStatus::TICKET_STATUS_PROCESS){

                        // La acción de anular requiere el motivo, campo status_reason
                        if (StringHelper::IsNullOrEmptyString($status_reason)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.required', ['attribute' => 'razón de la anulación']),
                                'error_code' => ErrorCodesEnum::missing_field,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'status_reason'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        // Check if there is a change and set data to save
                        if($ticket->status_id != $status_id){
                            $ticket->status_id = $status_id;
                            $changes['status_id'] = $status_id;

                            $ticket->rejected_at = Carbon::now();
                            $ticket->rejected_by_user = User::GetCreatedByUser($user);

                            $ticket->status_reason = $status_reason;
                            $changes['status_reason'] = $status_reason;
                            $changes['log_status_reason'] = $status_reason;
                        }

                    }

                }else{

                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => "Solo el usuario Visual Usuario puede anular un ticket",
                        'error_code' => ErrorCodesEnum::forbidden,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'status_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);

                }
            }



            // C. Resolver un ticket
            // Este paso se define como “reportar que el ticket ha sido completado”

            if($status_id == TicketStatus::TICKET_STATUS_RESOLVED){

                // Solo el usuario Responsable de Tienda (4) puede resolver un ticket
                if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                    // Un ticket puede ser “Resuelto” (3) solo si su estado actual es “En Proceso” (2)
                    if($ticket_clone->status_id == TicketStatus::TICKET_STATUS_PROCESS){

                        // La acción de resolver requiere el comentario de resolución, campo resolution_comment

                        if (StringHelper::IsNullOrEmptyString($resolution_comment)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.required', ['attribute' => 'comentario de resolución']),
                                'error_code' => ErrorCodesEnum::missing_field,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'resolution_comment'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        // Check if there is a change and set data to save
                        if($ticket->status_id != $status_id){
                            $ticket->status_id = $status_id;
                            $changes['status_id'] = $status_id;

                            $ticket->resolution_comment = $resolution_comment;
                            $changes['resolution_comment'] = $resolution_comment;
                            $changes['log_status_reason'] = $resolution_comment;
                        }

                    }

                }else{

                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => "Solo el usuario Responsable de Tienda puede resolver un ticket",
                        'error_code' => ErrorCodesEnum::forbidden,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'status_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);

                }
            }


            // D. Retornar un ticket “Resuelto” a “En Proceso”

            if($ticket_clone->status_id == TicketStatus::TICKET_STATUS_RESOLVED && $status_id == TicketStatus::TICKET_STATUS_PROCESS){

                // Solo el Visual Usuario puede revertir el estado del ticket de Resuelto a En progreso
                if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {

                    // La acción de cambiar a proceso requiere el motivo, campo status_reason

                    if (StringHelper::IsNullOrEmptyString($status_reason)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'status_reason']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'status_reason'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                     // Check if there is a change and set data to save
                     if($ticket->status_id != $status_id){
                        $ticket->status_id = $status_id;
                        $changes['status_id'] = $status_id;

                        $ticket->status_reason = $status_reason;
                        $changes['status_reason'] = $status_reason;
                        $changes['log_status_reason'] = $status_reason;
                    }

                }else{

                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => "Solo el Visual Usuario puede revertir el estado del ticket de Resuelto a En progreso",
                        'error_code' => ErrorCodesEnum::forbidden,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'status_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);

                }
            }



            // E. Cerrar un ticket
            // Este paso se define como “verificar que el ticket realmente haya sido completado”

            if($status_id == TicketStatus::TICKET_STATUS_CLOSED){

                // Solo el usuario “Visual Usuario” (2) puede cerrar un ticket
                if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {

                    // Un ticket puede ser “Cerrado” (4) solo si su estado actual es “Resuelto” (3)
                    if($ticket_clone->status_id == TicketStatus::TICKET_STATUS_RESOLVED){

                        // La acción de cerrar requiere el comentario de cierre, campo status_reason

                        if (StringHelper::IsNullOrEmptyString($status_reason)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.required', ['attribute' => 'status_reason']),
                                'error_code' => ErrorCodesEnum::missing_field,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'status_reason'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        // Check if there is a change and set data to save
                        if($ticket->status_id != $status_id){
                            $ticket->status_id = $status_id;
                            $changes['status_id'] = $status_id;

                            $ticket->status_reason = $status_reason;
                            $changes['status_reason'] = $status_reason;
                            $changes['log_status_reason'] = $status_reason;
                        }

                    }

                }  else {

                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => "Solo el usuario Visual Usuario puede cerrar un ticket",
                        'error_code' => ErrorCodesEnum::forbidden,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'status_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);

                }
            }
        }

        // ------------- Type ---------------------
        if($type_id != null) {
            if (StringHelper::IsNullOrEmptyString($type_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'tipo']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'type_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if (!ctype_digit($type_id)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'tipo']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'type_id',
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $ticket_type = TicketType::find($type_id);
            if (!$ticket_type) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'tipo']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'type_id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if there is a change and set data to save
            if($ticket->type_id != $type_id){
                $ticket->type_id = $type_id;
                $changes['type_id'] = $type_id;
            }

        }


        // ------------- Branch ID ---------------------
        if($branch_id != null) {
            if (StringHelper::IsNullOrEmptyString($branch_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'sucursal']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if (!ctype_digit($branch_id)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'sucursal']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'branch_id',
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $branch = Branch::find($branch_id);
            if (!$branch) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'sucursal']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }


            // [Check UserStoreBrand Permission]
            $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $branch_id,
                trans('api/validation.forbidden_entity_sb', ['entity' => 'sucursal']),
                $resource, 'branch_id');

            if ($error_response)
                return $error_response;


            // Check if there is a change and set data to save
            if($ticket->branch_id != $branch_id){
                $ticket->branch_id = $branch_id;
                $changes['branch_id'] = $branch_id;
            }
        }


        // ------------- Description ---------------------
        if($description != null) {

            // Check if there is a change and set data to save
            if ($ticket->description != $description) {
                $ticket->description = $description;
                $changes['description'] = $description;
            }

        }


        // ------------- Delivery Date ---------------------
        if (!StringHelper::IsNullOrEmptyString($delivery_date)) {

            // Check: delivery_date
            try {
                $x = Carbon::createFromTimestamp($delivery_date);

                $ticket->delivery_date = $x;
                $changes['delivery_date'] = $x;

            } catch (\Exception $exp) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'delivery_date']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'delivery_date'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // ------------- Sub Type ---------------------
        if($subtype_id != null) {

            if (StringHelper::IsNullOrEmptyString($subtype_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'sub tipo']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'subtype_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if (!ctype_digit(StringHelper::Trim($subtype_id))) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'sub tipo']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'subtype_id',
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $sub_type_data = TicketTypeSub::find($subtype_id);
            if (!$sub_type_data) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'sub tipo']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'subtype_id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }


            // Check if there is a change and set data to save
            if($ticket->subtype_id != $subtype_id){
                $ticket->subtype_id = $subtype_id;
                $changes['subtype_id'] = $subtype_id;
            }
        }


        // ------------- Priority ---------------------
        if($request->exists('priority_id')){

            if (StringHelper::IsNullOrEmptyString($priority_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'priority']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'priority_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }else {
                if (!ctype_digit($priority_id)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'priority']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'priority_id',
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $priority = Priority::find($priority_id);
            if(!$priority){
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'priority']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'priority_id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }


            // Check if there is a change and set data to save
            if ($ticket_clone->priority_id != $priority_id) {
                $ticket->priority_id = $priority_id;
                $changes['priority_id'] = $priority_id;
            }
        }

         // --------------------- Location ---------------------
         if($request->exists('location')){

             // Check if there is a change and set data to save
             if ($ticket_clone->location != $location) {
                $ticket->location = $location;
                $save['location'] = $location;
            }

         }


        // --------------------- resolution_comment ---------------------
        if($request->exists('resolution_comment')){

            // Check if there is a change and set data to save
            if ($ticket_clone->resolution_comment != $resolution_comment) {
                $ticket->resolution_comment = $resolution_comment;
                $save['resolution_comment'] = $resolution_comment;
            }

        }


        // ------------------- Rules for USER ---------------------------
        // Solo los algunos tipos de usuario pueden actualizar un ticket
        $pass = false;

        // Visual Usuario (2), cualquier ticket de cualquier sucursal
        if(!$pass){
            if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {
                $pass = true;
            }
        }

        // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        if(!$pass){

            if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $check = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $ticket_clone->branch_id)
                    ->first();

                if($check != null){
                    $pass = true;
                }
            }
        }

        // Cualquier tipo de usuario, solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        // y el ticket haya sido creado por el usuario
        if(!$pass){

            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $ticket_clone->branch_id)
                ->first();

            $username_from_ticket = strtolower(UserHelper::ExtractUserNameFromString($ticket_clone->created_by_user));
            $username_from_user = strtolower($user->username);

            if($check != null && $username_from_ticket == $username_from_user){
                $pass = true;
            }

        }

        if(!$pass){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public static function AddPhotosValidation(
        $request, $resource, $user,
        $ticket, &$photos_data)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $photos = $request->get('photos');
        $photos_data['photos'] = [];
        $role = $user->role->name;

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

        // [Check UserStoreBrand Permission ]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $ticket->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'ticket']),
            $resource, 'branch_id');

        if($error_response)
            return $error_response;


        // No permitir actualizar si el estado actual del ticket es Cerrado o Cancelado
        if ($ticket->status_id == TicketStatus::TICKET_STATUS_CLOSED || $ticket->status_id == TicketStatus::TICKET_STATUS_CANCELED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket_cannot_edit_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        // Check -> [Fotos]
        if(count($photos)>0){

            $photos_by_type_1 = [];
            $photos_by_type_2 = [];

            foreach ($photos as $item_photo) {

                $order = (isset($item_photo['order']) ? StringHelper::Trim($item_photo['order']): "");
                $guid = (isset($item_photo['guid']) ? StringHelper::Trim($item_photo['guid']): "");
                $name = (isset($item_photo['name']) ? StringHelper::Trim($item_photo['name']): "");
                $photo = (isset($item_photo['photo']) ? StringHelper::Trim($item_photo['photo']): "");
                $type = (isset($item_photo['type']) ? StringHelper::Trim($item_photo['type']): "");
                $is_new_photo = ($guid == null ? true : false);


                // Validations only for new data
                if ($is_new_photo) {

                    //-------------------------- Order -----------------------------------
                    // Check if the order field is an integer
                    if (!ctype_digit($order)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'order']),
                            'error_code' => ErrorCodesEnum::not_valid,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> order',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    // Check if the order field is allowed (1-6)
                    if (!($order >= 1 && $order <= 6)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.between.numeric', ['attribute' => 'order', 'min' => 1, 'max' => 6]),
                            'error_code' => ErrorCodesEnum::not_exists,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> order',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }

                    //-------------------------- Type -----------------------------------
                    if(!array_key_exists('type', $item_photo)){
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'type']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> type',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    // Check if the type field is an integer
                    if (!ctype_digit($type)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'type']),
                            'error_code' => ErrorCodesEnum::not_valid,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> type',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    // Check if the type field is allowed (1-2)
                    if (!($type >= 1 && $type <= 2)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'type']),
                            'error_code' => ErrorCodesEnum::not_exists,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> type',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }

                    //-------------------------- Name -----------------------------------
                    // Check if the field Name has data
                    if ($name == null) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'nombre']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> name'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    //-------------------------- Photo -----------------------------------
                    // Check if the field Photo has data
                    if ($photo == null) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'foto']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> photo',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    // Check if the field Photo is a valid image (base64)
                    if (!Base64Helper::CheckBase64StringIsValid($photo)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'photo no es una imagen (base64) o']),
                            'error_code' => ErrorCodesEnum::not_valid,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> photo',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    // Set data
                    $new_photo = [
                        'new' => true,
                        'order' => $order,
                        'guid' => FunctionHelper::CreateGUID(16),
                        'name' => $name,
                        'photo' => $photo,
                        'extension' => FileHelper::GetExtensionFromFilename($name),
                        'type' => $type,
                    ];
                    array_push($photos_data['photos'], $new_photo);

                    // set data array by type
                    $last_photo_element = end($photos_data['photos']);

                    if($type == 1){
                        array_push($photos_by_type_1, $last_photo_element);
                    } else if ($type == 2){
                        array_push($photos_by_type_2, $last_photo_element);
                    }

                } else {

                    //-------------------------- Guid -----------------------------------
                    // Check if exists GUID
                    $ticket_photo = TicketPhoto::where('guid', $guid)->first();

                    if ($ticket_photo == null) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_exists', ['attribute' => 'GUID: '.$guid]),
                            'error_code' => ErrorCodesEnum::not_exists,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'photos -> guid',
                                'photo_name' => $name
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }

                    $new_photo = [
                        'new' => false,
                        'order' => $order,
                        'guid' => $ticket_photo->guid,
                        'name' => null,
                        'photo' => null,
                        'extension' => null,
                        'type' => null,
                    ];
                    array_push($photos_data['photos'], $new_photo);
                }
            }


            //-------------------------- Limit by type -----------------------------------
            // Check the limit of the list of photos of type 1
            if (count($photos_by_type_1) > 6) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => 'El limite de fotos permitidas por tipo de estar entre 1 y 6 fotos.',
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'photos',
                        'type' => $type
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check the limit of the list of photos of type 2
            if (count($photos_by_type_2) > 6) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => 'El limite de fotos permitidas por tipo de estar entre 1 y 6 fotos.',
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'photos',
                        'type' => $type
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            //-------------------------- Check Duplicates by order -----------------------------------

            // Check if the order values has data duplicated of type 1
            if (count($photos_by_type_1) > 0) {
                $tempArr = array_unique(array_column($photos_by_type_1, 'order'));

                if (count($photos_by_type_1) != count($tempArr)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.ticket.photos.order_duplicated_by_type', ['attribute' => 'order', 'type' => 'tipo 1 (problema o incidente)']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'photos -> order'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Check if the order values has data duplicated of type 2
            if (count($photos_by_type_2) > 0) {
                $tempArr = array_unique(array_column($photos_by_type_2, 'order'));

                if (count($photos_by_type_2) != count($tempArr)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.ticket.photos.order_duplicated_by_type', ['attribute' => 'order', 'type' => 'tipo 2 (evidencia)']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'photos -> order'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

        }


        // ------------------- Rules for USER ---------------------------
        // Solo los algunos tipos de usuario pueden actualizar un ticket
        $pass = false;

        // Visual Usuario (2), cualquier ticket de cualquier sucursal
        if(!$pass){
            if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {
                $pass = true;
            }
        }

        // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        if(!$pass){

            if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $check = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $ticket->branch_id)
                    ->first();

                if($check != null){
                    $pass = true;
                }
            }
        }

        // Cualquier tipo de usuario, solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        // y el ticket haya sido creado por el usuario
        if(!$pass){

            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $ticket->branch_id)
                ->first();

            $username_from_ticket = strtolower(UserHelper::ExtractUserNameFromString($ticket->created_by_user));
            $username_from_user = strtolower($user->username);

            if($check != null && $username_from_ticket == $username_from_user){
                $pass = true;
            }

        }

        if(!$pass){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }


        return null;
    }

    public static function VideoProcessValidation($request, $resource, $user, $ticket, &$data)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $video1 = $request->file('video');
        $data = [];
        $role = $user->role->name;


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

        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $ticket->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'branch_id');

        if($error_response)
            return $error_response;


        // No permitir actualizar si el estado actual del ticket es Cerrado o Cancelado
        if ($ticket->status_id == TicketStatus::TICKET_STATUS_CLOSED || $ticket->status_id == TicketStatus::TICKET_STATUS_CANCELED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket_cannot_edit_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }


        // set data to array called "data"
        $action = null;
        if($video1 == null){
            $action = ActionEnum::DELETE;
        } else {
            if(StringHelper::IsNullOrEmptyString($ticket->video_guid)){
                $action = ActionEnum::CREATE;
            }else{
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
                        'message' => trans('api/validation.limit', ['attribute' => $max_size.' bytes']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'video'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Check video extensions
                if (!in_array(strtolower($video->extension()), self::$video_ext)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.video_ext_not_allowed', ['attribute' => implode(', ', self::$video_ext)]),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'video'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

         // ------------------- Rules for USER ---------------------------
        // Solo los algunos tipos de usuario pueden actualizar un ticket
        $pass = false;

        // Visual Usuario (2), cualquier ticket de cualquier sucursal
        if(!$pass){
            if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {
                $pass = true;
            }
        }

        // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        if(!$pass){

            if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $check = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $ticket->branch_id)
                    ->first();

                if($check != null){
                    $pass = true;
                }
            }
        }

        // Cualquier tipo de usuario, solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        // y el ticket haya sido creado por el usuario
        if(!$pass){

            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $ticket->branch_id)
                ->first();

            $username_from_ticket = strtolower(UserHelper::ExtractUserNameFromString($ticket->created_by_user));
            $username_from_user = strtolower($user->username);

            if($check != null && $username_from_ticket == $username_from_user){
                $pass = true;
            }

        }

        if(!$pass){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }
}
