<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/17/2019
 * Time: 10:47 AM
 */

namespace App\Http\Controllers\Api\CheckList\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ChecklistEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\ArrayHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\Base64Helper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Checklist\ChecklistItem;
use App\Models\Checklist\ChecklistItemDetail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ChecklistItemDetailsValidation
{
    public static $video_ext = ['mp4', 'mpeg'];

    public static function CreateValidation($request, $resource, $user, $checklist)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $role = $user->role;
        $items = $request->all()['items'];


        // [Checklist]
        if (!$checklist) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'checklist_id']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $checklist->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'branch_id');

        if($error_response)
            return $error_response;

        // check the user role
        if (strtolower($role->name) != strtolower(UserRoleEnum::VISUAL)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'role'
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        // [Items]
        if (count($items) == 0) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required_list_one', ['attribute' => 'items de verificación del checklist']),
                'error_code' => ErrorCodesEnum::required_list,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'items',
                ]
            ], Response::HTTP_BAD_REQUEST);
        } else {

            // Check if the item_id values has data duplicated
            $tempArr = array_unique(array_column($items, 'item_id'));

            if (count($items) != count($tempArr)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.duplicated', ['attribute' => 'item_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'item_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }


            foreach ($items as $item) {

                $checklist_item_id = (isset($item['item_id']) ? StringHelper::Trim($item['item_id']): "");
                $disagreement = (isset($item['disagreement']) ? StringHelper::Trim($item['disagreement']): "");
                $disagreement_reason = (isset($item['disagreement_reason']) ? StringHelper::Trim($item['disagreement_reason']): "");
                $disagreement_generate_ticket = (isset($item['disagreement_generate_ticket']) ? StringHelper::Trim($item['disagreement_generate_ticket']): "");

                // Check checklist item ID
                if (StringHelper::IsNullOrEmptyString($checklist_item_id)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.required', ['attribute' => 'item_id']),
                        'error_code' => ErrorCodesEnum::missing_field,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'item_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }else {
                    if (!ctype_digit($checklist_item_id)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'item_id']),
                            'error_code' => ErrorCodesEnum::not_valid,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'item_id'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }

                // Check if checklist item ID exists
                $checklist_item = ChecklistItem::find($checklist_item_id);
                if (!$checklist_item) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_exists', ['attribute' => 'item_id']),
                        'error_code' => ErrorCodesEnum::not_exists,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'item_id'
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }


                // Check if item already exists in checklist_item_details
                $checklist_item_detail = ChecklistItemDetail::
                    where('checklist_id', $checklist->id)->
                    where('checklist_item_id', $checklist_item_id)->first();

                if ($checklist_item_detail != null) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.already_exists', ['attribute' => 'item_id '. $checklist_item_id]),
                        'error_code' => ErrorCodesEnum::already_exists,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'item_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }

                // [Disagreement]
                if (StringHelper::IsNullOrEmptyString($disagreement)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.required', ['attribute' => 'disagreement']),
                        'error_code' => ErrorCodesEnum::missing_field,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'disagreement'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }else {
                    if (!ctype_digit($disagreement) || ($disagreement !== '0' && $disagreement !== '1')) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'disagreement']),
                            'error_code' => ErrorCodesEnum::not_valid,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'disagreement'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }


                // Generate Ticket
                if ($disagreement == '1') {
                    if (StringHelper::IsNullOrEmptyString($disagreement_generate_ticket)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'disagreement_generate_ticket']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'disagreement_generate_ticket'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }

            }
        }

        return null;
    }

    public static function UpdateValidation($request, $resource, $user, $checklist)
    {
        $role = $user->role;

        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $items = $request->all()['items'];


        // --------------------------------------------
        // Validations
        // --------------------------------------------

        // [Checklist]
        if (!$checklist) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'checklist_id']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $checklist->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'branch_id');

        if($error_response)
            return $error_response;

        // check the user role
        if (strtolower($role->name) != strtolower(UserRoleEnum::VISUAL)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'role'
                ]
            ], Response::HTTP_FORBIDDEN);
        }


        // Check if checklist status is approved
        if ($checklist->checklist_status_id == ChecklistEnum::CHECKLIST_STATUS_APPROVED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.checklist.cannot_edit'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        // [Items]
        if (isset($items)) {

            if (count($items) == 0) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required_list_one', ['attribute' => 'items de verificación del checklist']),
                    'error_code' => ErrorCodesEnum::required_list,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'items',
                    ]
                ], Response::HTTP_BAD_REQUEST);
            } else {

                // Check if the item_id values has data duplicated
                $tempArr = array_unique(array_column($items, 'item_id'));

                if (count($items) != count($tempArr)) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.duplicated', ['attribute' => 'item_id']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'item_id'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }


                foreach ($items as $item) {

                    $checklist_item_id = (isset($item['item_id']) ? StringHelper::Trim($item['item_id']): "");
                    $disagreement = (isset($item['disagreement']) ? StringHelper::Trim($item['disagreement']): "");
                    $disagreement_reason = (isset($item['disagreement_reason']) ? StringHelper::Trim($item['disagreement_reason']): "");
                    $disagreement_generate_ticket = (isset($item['disagreement_generate_ticket']) ? StringHelper::Trim($item['disagreement_generate_ticket']): "");

                    // Check checklist item ID
                    if (StringHelper::IsNullOrEmptyString($checklist_item_id)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'item_id']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'item_id'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }else {
                        if (!ctype_digit($checklist_item_id)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.not_valid', ['attribute' => 'item_id']),
                                'error_code' => ErrorCodesEnum::not_valid,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'item_id'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    }

                    // Check if checklist item ID exists
                    $checklist_item = ChecklistItem::find($checklist_item_id);
                    if (!$checklist_item) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_exists', ['attribute' => 'item_id']),
                            'error_code' => ErrorCodesEnum::not_exists,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'item_id'
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }

                    // [Disagreement ]
                    if (StringHelper::IsNullOrEmptyString($disagreement)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.required', ['attribute' => 'disagreement']),
                            'error_code' => ErrorCodesEnum::missing_field,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'disagreement'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }else {
                        if (!ctype_digit($disagreement) || ($disagreement != 0 && $disagreement != 1)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.not_valid', ['attribute' => 'disagreement']),
                                'error_code' => ErrorCodesEnum::not_valid,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'disagreement'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    }


                    // Generate Ticket
                    if ($disagreement == 1) {
                        if (StringHelper::IsNullOrEmptyString($disagreement_generate_ticket)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.required', ['attribute' => 'disagreement_generate_ticket']),
                                'error_code' => ErrorCodesEnum::missing_field,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'disagreement_generate_ticket'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        if (!ctype_digit($disagreement_generate_ticket) || ($disagreement_generate_ticket != 0 && $disagreement_generate_ticket != 1)) {
                            return response()->json([
                                'object' => AppObjectNameEnum::ERROR,
                                'message' => trans('api/validation.not_valid', ['attribute' => 'disagreement_generate_ticket']),
                                'error_code' => ErrorCodesEnum::not_valid,
                                'error_data' => [
                                    'resource' => $resource,
                                    'field' => 'disagreement_generate_ticket'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                    }

                }
            }
        }

        return null;
    }

    public static function PhotoProcessValidation($request, $resource, $user, $checklist, $item_id, &$photos)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $photo1_name =  StringHelper::Trim($request->get('photo1_name'));
        $photo1_base64 = StringHelper::Trim($request->get('photo1_base64'));
        $photo2_name = StringHelper::Trim($request->get('photo2_name'));
        $photo2_base64 = StringHelper::Trim($request->get('photo2_base64'));
        $photo3_name = StringHelper::Trim($request->get('photo3_name'));
        $photo3_base64 = StringHelper::Trim($request->get('photo3_base64'));

        $photos = [];

        // --------------------------------------------
        // Validations
        // --------------------------------------------

        // [Checklist]
        if (!$checklist) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Check checklist_item_details
        $checklist_item_detail = ChecklistItemDetail::where('checklist_id', $checklist->id)
            ->where('checklist_item_id', $item_id)->first();

        if (!$checklist_item_detail) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'item_id']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'checklist_item_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $checklist->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'branch_id');

        if($error_response)
            return $error_response;

        // Check if checklist status is approved
        if ($checklist->checklist_status_id == ChecklistEnum::CHECKLIST_STATUS_APPROVED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.checklist.cannot_edit'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        // Check photo fields

        // Photo 1
        array_push($photos, [
            'action' => self::CheckActionForPhoto($photo1_name, $photo1_base64),
            'order' => 1,
            'name' => $photo1_name,
            'base64' => $photo1_base64
        ]);

        // Photo 2
        array_push($photos, [
            'action' => self::CheckActionForPhoto($photo2_name, $photo2_base64),
            'order' => 2,
            'name' => $photo2_name,
            'base64' => $photo2_base64
        ]);

        // Photo 3
        array_push($photos, [
            'action' => self::CheckActionForPhoto($photo3_name, $photo3_base64),
            'order' => 3,
            'name' => $photo3_name,
            'base64' => $photo3_base64
        ]);

        // remove photo items that have the key "action" as keep
        $photos = ArrayHelper::removeElementWithValue($photos, "action", ActionEnum::KEEP);

        foreach ($photos as $item) {

            $key_base64 = 'photo' . $item['order'] . '_base64';

            // Utils edit a photo
            if ($item['action'] == ActionEnum::EDIT) {

                // Check if photo (base64) is required
                if (StringHelper::IsNullOrEmptyString($item['base64'])) {

                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.required', ['attribute' => $key_base64]),
                        'error_code' => ErrorCodesEnum::missing_field,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => $key_base64
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Check if is an image (base64)
                if (!Base64Helper::CheckBase64StringIsValid($item['base64'])) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => $key_base64 . ' no es una imagen (base64) o']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => $key_base64
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

        }

        return null;
    }

    public static function VideoProcessValidation($request, $resource, $user, $checklist, $item_id, &$data)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $video1 = $request->file('video');
        $data = [];


        // --------------------------------------------
        // Validations
        // --------------------------------------------

        // [Checklist]
        if (!$checklist) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // Check checklist_item_details
        $checklist_item_detail = ChecklistItemDetail::where('checklist_id', $checklist->id)
            ->where('checklist_item_id', $item_id)->first();

        if (!$checklist_item_detail) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'item_id']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'checklist_item_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $checklist->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'branch_id');

        if($error_response)
            return $error_response;

        // Check if checklist status is approved
        if ($checklist->checklist_status_id == ChecklistEnum::CHECKLIST_STATUS_APPROVED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.checklist.cannot_edit'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        // set data to array called "data"
        $action = null;
        if($video1 == null){
            $action = ActionEnum::DELETE;
        } else {
            if(StringHelper::IsNullOrEmptyString($checklist_item_detail->video_guid)){
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


        return null;
    }

    public static function GetValidation($request, $resource, $user, $checklist)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $sub_type = $request->get('sub_type');
        $item_id = $request->get('item_id');
        $all = $request->get('all');

        // --------------------------------------------
        // Validations
        // --------------------------------------------

        // -------------------- Check: checklist --------------------
        if (!$checklist) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'checklist_id']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // -------------------- [Check UserStoreBrand Permission ] --------------------
        $error_response = GlobalValidation::CheckUserStoreBranchPermission(
            $user, $checklist->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'id');

        if($error_response)
            return $error_response;


        // -------------------- Sub Type --------------------
        if($request->exists('sub_type')) {

            if (!ctype_digit($sub_type)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'sub_type']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'sub_type'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        }


        // -------------------- Item ID --------------------
        if($request->exists('item_id')) {

            if (!ctype_digit($item_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'item_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'item_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        }


        // -------------------- All --------------------
        if($request->exists('all')) {

            if (!ctype_digit($all)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'all']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'sub_type'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($all !== '0' && $all !== '1') {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'all']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'all'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // La lógica de este parámetro "all" debe funcionar solo si el parámetro item_id no ha sido especificado. Ambos parámetros no pueden funcionar juntos.
            if($request->exists('item_id')) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => "parametro item_id no debe ser utilizado cuando el parametro [all] se ha especificado, ambos parámetros no pueden funcionar juntos.",
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'item_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        }


        return null;
    }

    private static function CheckActionForPhoto($name, $base64)
    {
        $return = null;

        // For add
        if($return == null){
           if($name != null && $base64 == null){
                $return = ActionEnum::KEEP;
           }
        }

        // For delete
        if($return == null){
            if($name == null && $base64 == null){
                $return = ActionEnum::DELETE;
            }
        }

        // For Edit
        if($return == null){
            if($name != null && $base64 != null){
                $return = ActionEnum::EDIT;
            }
        }

        return $return;
    }
}
