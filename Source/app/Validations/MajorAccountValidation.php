<?php

namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Models\MajorAccount;
use App\Models\User;
use Illuminate\Http\Response;

class MajorAccountValidation extends BaseValidation
{
    public function index($request, $resource, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);

        $params = [
            'user' => $user
        ];


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::LIST);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    public function checkMajorAccount($resource, $major_account_id, $field)
    {
        if (StringHelper::IsNullOrEmptyString($major_account_id)) {

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

            if (!ctype_digit($major_account_id)) {
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


            $major_account = MajorAccount::find($major_account_id);
            if(!$major_account){
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

        }

        return null;
    }

    public function checkRoleIsAllowed($resource, $role, $action)
    {
        if ($action == ActionEnum::LIST) {

            if ($role == UserRoleEnum::PROVEEDOR)
            {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.forbidden_role_user'),
                    'error_code' => ErrorCodesEnum::forbidden,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'user'
                    ]
                ], Response::HTTP_FORBIDDEN);
            }

        }

        return null;
    }

}
