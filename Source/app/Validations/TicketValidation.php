<?php

namespace App\Validations;

use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\BranchLocation;
use App\Models\Priority;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketStatus;
use App\Models\Ticket\TicketType;
use App\Models\Ticket\TicketTypeSub;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Models\Views\TicketSearch;
use App\Validations\BaseValidation;
use Carbon\Carbon;
use Illuminate\Http\Response;

class TicketValidation extends BaseValidation
{
    public function store($request, $resource, $user, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $priority_id = StringHelper::Trim($request->get('priority_id'));
        $type_id = StringHelper::Trim($request->get('type_id'));
        $branch_location_id = StringHelper::Trim($request->get('branch_location_id'));
        $description = StringHelper::Trim($request->get('description'));
        $delivery_date = StringHelper::Trim($request->get('delivery_date'));
        $subtype_id = StringHelper::Trim($request->get('subtype_id'));
        $location = StringHelper::Trim($request->get('location'));
        $reference_doc = StringHelper::Trim($request->get('reference_doc'));

        $save = [
            'priority_id' => null,
            'status_id' => TicketStatus::STATUS_NUEVO,
            'status_reason' => null,
            'type_id' => null,
            'branch_location_id' => null,
            'description' => $description,
            'delivery_date' => null,
            'subtype_id' => null,
            'location' => null,
            'approved_at' => null,
            'approved_by_user' => null,
            'rejected_at' => null,
            'rejected_by_user' => null,
            'reference_doc' => null,
        ];

        // By Role
        $error_response = $this->checkRoleIsAllowed($resource, $user, ActionEnum::CREATE);

        if ($error_response)
            return $error_response;

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
        } else {
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
        if (!$priority) {
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

        // Set data to save
        $save['type_id'] = $type_id;

        // ------------- Branch Location ID ---------------------
        if (StringHelper::IsNullOrEmptyString($branch_location_id)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'branch_location_id']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'branch_location_id'
                ]
            ], Response::HTTP_BAD_REQUEST);
        } else {
            if (!ctype_digit($branch_location_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'branch_location_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_location_id',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $branch_location = BranchLocation::find($branch_location_id);
        if (!$branch_location) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'branch_location_id']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'branch_location_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission(
            $user,
            $branch_location->branch_branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'sucursal']),
            $resource,
            'branch_id'
        );

        if ($error_response)
            return $error_response;


        // Set data to save
        $save['branch_location_id'] = $branch_location_id;


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
        } else {
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

        // Set data to save
        $save['subtype_id'] = $subtype_id;

        // Location
        if ($request->exists('location')) {
            $save['location'] = $location;
        }

        // Location
        if ($request->exists('reference_doc')) {
            $save['reference_doc'] = $reference_doc;
        }

        return null;
    }

    public function index($request, $resource)
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

    public function show($resource, $user, $ticketId)
    {
        $ticket = Ticket::find($ticketId);

        // -------------------------------------------
        // Get Data From Request
        // --------------------------------------------        
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
        $error_response = GlobalValidation::CheckUserStoreBranchPermission(
            $user,
            $ticket->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'ticket']),
            $resource,
            'id'
        );

        if ($error_response)
            return $error_response;


        // Check data
        $counter = TicketSearch::where('id', $ticket->id)->filterByRole($user)->count();

        if ($counter == 0) {
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

    public function update($request, $resource, $user, &$ticket, &$changes)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $status_id = ($request->get('status_id') !== null ? StringHelper::Trim($request->get('status_id')) : null);
        $type_id = ($request->get('type_id') !== null ? StringHelper::Trim($request->get('type_id')) : null);
        $branch_location_id = ($request->get('branch_location_id') !== null ? StringHelper::Trim($request->get('branch_location_id')) : null);
        $description = ($request->get('description') !== null ? StringHelper::Trim($request->get('description')) : null);
        $status_reason = ($request->get('status_reason') !== null ? StringHelper::Trim($request->get('status_reason')) : null);
        $delivery_date = ($request->get('delivery_date') !== null ? StringHelper::Trim($request->get('delivery_date')) : null);
        $subtype_id = ($request->get('subtype_id') !== null ? StringHelper::Trim($request->get('subtype_id')) : null);
        $priority_id = StringHelper::Trim($request->get('priority_id'));
        $location = StringHelper::Trim($request->get('location'));
        $reference_doc = StringHelper::Trim($request->get('reference_doc'));

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

        // By Role
        $error_response = $this->checkRoleIsAllowed($resource, $user, ActionEnum::EDIT, $ticket_clone);

        if ($error_response)
            return $error_response;

        // Check Ticket
        $error_response = $this->checkIfTicketCanBeUpdated($resource, $ticket_clone, 'status_id');

        if ($error_response)
            return $error_response;

        // Rule by status                       
        if ($status_id != null) {
            $error_response = $this->checkIfStatusCanBeUpdated($resource, 'status_id', $status_id, $user, $ticket_clone);

            if ($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('status_id', $ticket_clone->status_id, $status_id, $ticket, $changes);

            $error_response = $this->checkStatusReason($resource, 'status_reason', $status_id, $status_reason, $ticket, $user, $changes);

            if ($error_response)
                return $error_response;
        }
        
        // ------------- Type ---------------------
        if ($type_id != null) {
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
            if ($ticket->type_id != $type_id) {
                $ticket->type_id = $type_id;
                $changes['type_id'] = $type_id;
            }
        }


        // ------------- Branch ID ---------------------
        if ($branch_location_id != null) {
            if (StringHelper::IsNullOrEmptyString($branch_location_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'branch_location_id']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_location_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            } else {
                if (!ctype_digit($branch_location_id)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'branch_location_id']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'branch_location_id',
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $branch_location = BranchLocation::find($branch_location_id);
            if (!$branch_location) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'branch_location_id']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'branch_location_id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }


            // [Check UserStoreBrand Permission]
            $error_response = GlobalValidation::CheckUserStoreBranchPermission(
                $user,
                $branch_location->branch_branch_id,
                trans('api/validation.forbidden_entity_sb', ['entity' => 'sucursal']),
                $resource,
                'branch_id'
            );

            if ($error_response)
                return $error_response;


            // Check if there is a change and set data to save
            if ($ticket->branch_location_id != $branch_location_id) {
                $ticket->branch_location_id = $branch_location_id;
                $changes['branch_location_id'] = $branch_location_id;
            }
        }


        // ------------- Description ---------------------
        if ($description != null) {

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
        if ($subtype_id != null) {

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
            if ($ticket->subtype_id != $subtype_id) {
                $ticket->subtype_id = $subtype_id;
                $changes['subtype_id'] = $subtype_id;
            }
        }


        // ------------- Priority ---------------------
        if ($request->exists('priority_id')) {

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
            } else {
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
            if (!$priority) {
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
        if ($request->exists('location')) {

            // Check if there is a change and set data to save
            if ($ticket_clone->location != $location) {
                $ticket->location = $location;
                $save['location'] = $location;
            }
        }


        // --------------------- reference_doc ---------------------
        if ($request->exists('reference_doc')) {

            // Check if there is a change and set data to save
            if ($ticket_clone->reference_doc != $reference_doc) {
                $ticket->reference_doc = $reference_doc;
                $save['reference_doc'] = $reference_doc;
            }
        }

        return null;
    }

    // ---------------------------------------------------------------------------------------
    // Private functions 
    // ---------------------------------------------------------------------------------------
    public function checkRoleIsAllowed($resource, $user, $action, $data = null)
    {
        $role = $user->role->name;

        $forbiddenResponse = [
            'object' => AppObjectNameEnum::ERROR,
            'message' => trans('api/validation.forbidden_role_user'),
            'error_code' => ErrorCodesEnum::forbidden,
            'error_data' => [
                'resource' => $resource,
                'field' => 'user'
            ]
        ];

        if ($action == ActionEnum::CREATE) {

            if (!($role === UserRoleEnum::ADMIN ||
                $role === UserRoleEnum::GESTOR_INFRAESTRUCTURA ||
                $role === UserRoleEnum::RESPONSABLE_SEDE)) {
                return response()->json($forbiddenResponse, Response::HTTP_FORBIDDEN);
            }
        } elseif ($action == ActionEnum::EDIT) {

            if (!($role === UserRoleEnum::ADMIN ||
                $role === UserRoleEnum::GESTOR_INFRAESTRUCTURA ||
                $role === UserRoleEnum::RESPONSABLE_SEDE)) {
                return response()->json($forbiddenResponse, Response::HTTP_FORBIDDEN);
            }

            if ($role === UserRoleEnum::RESPONSABLE_SEDE) {
                $branch_location = BranchLocation::find($data->branch_location_id);
                $count = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $branch_location->branch_branch_id)
                    ->count();

                if ($count == 0)
                    return response()->json($forbiddenResponse, Response::HTTP_FORBIDDEN);
            }
        }

        return null;
    }

    public function checkIfTicketCanBeUpdated($resource, $data, $fieldName)
    {
        if (!($data->status_id == TicketStatus::STATUS_NUEVO)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket.cannot_edit_due_to_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $fieldName
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    public function checkIfStatusCanBeUpdated($resource, $fieldName, $statusId, $user, $data)
    {
        $role = $user->role->name;

        if (StringHelper::IsNullOrEmptyString($statusId)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'estado']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $fieldName
                ]
            ], Response::HTTP_BAD_REQUEST);
        } else {
            if (!ctype_digit(StringHelper::Trim($statusId))) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'estado']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $fieldName,
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $ticket_status = TicketStatus::find($statusId);
        if (!$ticket_status) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'estado']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $fieldName
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // ................... Rules ....................
        if (!($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.user_allowed_to_update_role_field', ['role' => 'gestor de infraestructura', 'field' => $fieldName]),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_FORBIDDEN);
        }


        if (!($data->status_id == TicketStatus::STATUS_NUEVO)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket.cannot_edit_due_to_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $fieldName
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        if (!($statusId == TicketStatus::STATUS_CONFIRMADO || $statusId == TicketStatus::STATUS_ANULADO)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket.only_update_status', ['status' => 'confirmado o anulado']),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $fieldName
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkStatusReason($resource, $fieldName, $statusId, $statusReason, $ticket, $user, &$changes)
    {        
        if($statusId == TicketStatus::STATUS_CONFIRMADO) 
        {
            $ticket->approved_at = Carbon::now();
            $ticket->approved_by_user = User::GetCreatedByUser($user);  
            $ticket->status_reason = $statusReason;

            $changes['status_reason'] = $statusReason;
            $changes['log_status_reason'] = $statusReason;
        } 
        else if($statusId == TicketStatus::STATUS_ANULADO) 
        {
            if (StringHelper::IsNullOrEmptyString($statusReason)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'razón de la anulación']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $fieldName
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $ticket->rejected_at = Carbon::now();
            $ticket->rejected_by_user = User::GetCreatedByUser($user);               
            $ticket->status_reason = $statusReason;
            
            $changes['status_reason'] = $statusReason;
            $changes['log_status_reason'] = $statusReason;
        } 

        return null;     
    }
}
