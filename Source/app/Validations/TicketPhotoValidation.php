<?php

namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Models\Ticket\TicketPhoto;
use FunctionHelper;
use Illuminate\Http\Response;

class TicketPhotoValidation
{
    public static function store(
        $request,
        $resource,
        $user,
        $ticket,
        &$photos_data
    ) {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $photos = $request->get('photos');
        $photos_data['photos'] = [];

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

        // Check -> [Fotos]
        if (count($photos) > 0) {

            foreach ($photos as $item_photo) {

                $order = (isset($item_photo['order']) ? StringHelper::Trim($item_photo['order']) : "");
                $guid = (isset($item_photo['guid']) ? StringHelper::Trim($item_photo['guid']) : "");
                $name = (isset($item_photo['name']) ? StringHelper::Trim($item_photo['name']) : "");
                $photo = (isset($item_photo['photo']) ? StringHelper::Trim($item_photo['photo']) : "");
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
                    ];
                    array_push($photos_data['photos'], $new_photo);
                } else {

                    //-------------------------- Guid -----------------------------------
                    // Check if exists GUID
                    $ticket_photo = TicketPhoto::where('guid', $guid)->first();

                    if ($ticket_photo == null) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_exists', ['attribute' => 'GUID: ' . $guid]),
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


            //-------------------------- Limit -----------------------------------          
            if (count($photos_data['photos']) > 6) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => 'El limite de fotos permitidas por tipo de estar entre 1 y 6 fotos.',
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'photos',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            //-------------------------- Check Duplicates by order -----------------------------------            
            if (count($photos_data['photos']) > 0) {
                $tempArr = array_unique(array_column($photos_data['photos'], 'order'));

                if (count($photos_data['photos']) != count($tempArr)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.duplicated', ['attribute' => 'order']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'photos -> order'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        return null;
    }
}
