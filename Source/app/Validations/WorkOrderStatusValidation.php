<?php


namespace App\Validations;


use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Models\WorkOrder\WorkOrderStatus;
use Illuminate\Http\Response;

class WorkOrderStatusValidation
{

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------
    public function checkStatus($resource, $id, $field)
    {
        if (!ctype_digit($id)) {
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


        $data = WorkOrderStatus::find($id);
        if(!$data){
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

        return null;
    }
}
