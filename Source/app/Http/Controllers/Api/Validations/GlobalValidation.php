<?php namespace App\Http\Controllers\Api\Validations;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use Illuminate\Http\Response;

class GlobalValidation
{

    public static function CheckUserStoreBranchPermission($user, $branch_id, $message, $resource, $field)
    {
        if(self::UserNeedToFilterData($user)) {
            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $branch_id)
                ->first();

            if (!$check) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => $message,
                    'error_code' => ErrorCodesEnum::forbidden,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field
                    ]
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return null;

    }

    public static function UserNeedToFilterData($user){

        $role = $user->role->name;
        $return = false;
        
        if($role == UserRoleEnum::RESPONSABLE_SEDE) {
            $return = true;
        }
        
        return $return;
    }

}