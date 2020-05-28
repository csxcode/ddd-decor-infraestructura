<?php

namespace App\Validations;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\BranchLocation;
use Illuminate\Http\Response;

class BranchLocationValidation
{
    public static function checkBranchLocation($resource, $value, $user, $field)
    {
        if (StringHelper::IsNullOrEmptyString($value)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);

        } else {

            if (!ctype_digit($value)) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field,
                    ]
                ], Response::HTTP_BAD_REQUEST);

            }


            $branch_location = BranchLocation::find($value);

            if(!$branch_location){

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => $field]),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field
                    ]
                ], Response::HTTP_NOT_FOUND);

            }


            // [Check UserStoreBrand Permission]
            $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $branch_location->branch_branch_id,
                trans('api/validation.forbidden_entity_sb', ['entity' => 'sucursal']),
                $resource, $field);

            if ($error_response)
                return $error_response;

        }

        return null;
    }

}
