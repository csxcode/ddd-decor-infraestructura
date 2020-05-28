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
use App\Enums\ModuleEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Branch;
use App\Models\CheckList\ChecklistStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ChecklistItemTypeValidation
{
    public static function AllValidation($request)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $checklist_id = StringHelper::Trim($request->get('checklist_id'));
        $types = StringHelper::Trim($request->get('types'));


        // --------------------------------------------
        // Validations
        // --------------------------------------------

        // Check: checklist_id
        if (!StringHelper::IsNullOrEmptyString($checklist_id)) {

            if (!ctype_digit($checklist_id)) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'checklist_id']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => AppObjectNameEnum::CHECKLIST_ITEM_TYPE,
                        'field' => 'checklist_id'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }


        // Check: types
        if (!StringHelper::IsNullOrEmptyString($types)) {
            if (!ctype_digit($types) || ($types !== '0' && $types !== '1')) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'types']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => AppObjectNameEnum::CHECKLIST_ITEM_TYPE,
                        'field' => 'types'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return null;
    }
}