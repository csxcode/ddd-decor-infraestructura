<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/17/2019
 * Time: 10:47 AM
 */

namespace App\Http\Controllers\Api\CheckList\Validations;

use App\Enums\AppObjectNameEnum;
use App\Enums\ChecklistEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Branch;
use App\Models\Checklist\ChecklistStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ChecklistValidation
{
    public static function CreateValidation($request, $resource, $user)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $role = $user->role;
        $branch_id = StringHelper::Trim($request->get('branch_id'));

        // [Branch]
        if (StringHelper::IsNullOrEmptyString($branch_id)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'branch']),
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
                    'message' => trans('api/validation.not_valid', ['attribute' => 'branch']),
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
                'message' => trans('api/validation.not_exists', ['attribute' => 'Branch']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'branch_id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // [Check UserStoreBrand Permission]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $branch_id,
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

        return null;
    }

    public static function AllValidation($request, $resource)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $store_id = StringHelper::Trim($request->get('store_id'));
        $branch_id = StringHelper::Trim($request->get('branch_id'));
        $status = StringHelper::Trim($request->get('status'));
        $disagreement = StringHelper::Trim($request->get('disagreement'));
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


        // Check: status
        if (!StringHelper::IsNullOrEmptyString($status)) {
            if (!ctype_digit($status)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'status']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // Check: disagreement
        if (!StringHelper::IsNullOrEmptyString($disagreement)) {
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

    public static function UpdateValidation(
        $request, $resource, $user,
        &$checklist, &$changes
    )
    {       

        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $branch_id = $request->get('branch_id');
        $status = $request->get('status');
        $status_reason = $request->get('status_reason');        
        $role = $user->role;        

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

        // check the user role
        if (strtolower($role->name) != strtolower(UserRoleEnum::VISUAL) && strtolower($role->name) != strtolower(UserRoleEnum::STORE_MANAGER)) {
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


        // ----------------------------------------------------------------
        // Visual and store_manager role can update these fields
        // ---------------------------------------------------------------- 

        // [Status]
        if ($status !== null) {

            $status = StringHelper::Trim($status);
            if (!ctype_digit($status)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'estado del checklist']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check if exists
            $checklist_status = ChecklistStatus::find($status);
            if (!$checklist_status) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'estado del checklist']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            if ($status == ChecklistEnum::CHECKLIST_STATUS_APPROVED) {
                $checklist->approved_by_user = User::GetCreatedByUser($user);
                $checklist->approved_at = Carbon::now();
            } else if ($status == ChecklistEnum::CHECKLIST_STATUS_REJECTED) {
                $checklist->rejected_by_user = User::GetCreatedByUser($user);
                $checklist->rejected_at = Carbon::now();
            }


            // Check if there is a change and add log change
            if ($checklist->checklist_status_id != $status) {
                $checklist->checklist_status_id = $status;
                $changes['checklist_status_id'] = $status;
            }

        }

        // Check for reason
        if ($status == ChecklistEnum::CHECKLIST_STATUS_REJECTED) {
            if (StringHelper::IsNullOrEmptyString($status_reason)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.required', ['attribute' => 'motivo de la actualización']),
                    'error_code' => ErrorCodesEnum::missing_field,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'status_reason'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check if there is a change and add log change
            if ($checklist->status_reason != $status_reason) {
                $checklist->status_reason = $status_reason;
                $changes['status_reason'] = $status_reason;
            }
        }

        // --------------------------------------------------
        // Validations for Visual
        // --------------------------------------------------
        if (strtolower($role->name) == strtolower(UserRoleEnum::VISUAL)) {
                                                
             // edit_status        
            if($request->exists('edit_status')){
                
                $edit_status = StringHelper::Trim($request->get('edit_status'));

                if (!ctype_digit($edit_status) || ($edit_status !== '0' && $edit_status !== '1')) {
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/validation.not_valid', ['attribute' => 'edit_status del checklist']),
                        'error_code' => ErrorCodesEnum::not_valid,
                        'error_data' => [
                            'resource' => $resource,
                            'field' => 'edit_status'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
                
                 // Check if there is a change and add log change
                 if ($checklist->edit_status != $edit_status) {
                    $checklist->edit_status = $edit_status;
                    $changes['edit_status'] = $edit_status;
                }
            }

             
            // [Branch]
            if($request->exists('branch_id')){                

                if ($branch_id !== null) {

                    $branch_id = StringHelper::Trim($branch_id);

                    if (!ctype_digit($branch_id)) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_valid', ['attribute' => 'branch']),
                            'error_code' => ErrorCodesEnum::not_valid,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'branch_id',
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    }                   

                    $branch = Branch::find($branch_id);

                    if(!$branch){
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/validation.not_exists', ['attribute' => 'branch']),
                            'error_code' => ErrorCodesEnum::not_exists,
                            'error_data' => [
                                'resource' => $resource,
                                'field' => 'branch_id'
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }                 

                    // Check if there is a change and add log change
                    if($checklist->branch_id != $branch_id){
                        $checklist->branch_id = $branch_id;
                        $changes['branch_id'] = $branch_id;
                    }
                    
                }
            }

        }   


        // --------------------------------------------------
        // Validations for Store_Manager
        // --------------------------------------------------
        if (strtolower($role->name) == strtolower(UserRoleEnum::STORE_MANAGER)) {

            // [Check UserStoreBrand Permission]
            $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $checklist->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
            $resource, 'branch_id '.$checklist->branch_id);

            if ($error_response)
                return $error_response;                                                                  
       }   

    }                      
}