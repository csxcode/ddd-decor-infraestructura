<?php

namespace Application\Api\Controllers;

use Shared\Enums\AccessTypeEnum;
use Shared\AppObjectNameEnum;
use Shared\ErrorCodesEnum;
use Shared\SessionStateEnum;
use Application\Helpers\ErrorHelper;
use Application\Helpers\TokenHelper;
use Application\Http\Controllers\Api\Validations\GlobalValidation;
use Application\Http\Controllers\Controller;
use Application\Models\Session;
use Application\Models\User;
use Application\Models\UserStoreBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function Login(Request $request)
    {
        $user = null;
        $token = null;

        try{
            $user = User::where('username', $request->input('username'))->first();

            if($user){
                if(!Hash::check($request->input('password'), $user->password)){

                    // Registrar intentos de inicio de sesión fallidos
                    Session::LogFailedLoginAttempts($user, AccessTypeEnum::Api);
                    $user = null;
                }
            }

            if (is_null($user)) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/auth.invalid_credentials'),
                    'error_code' => ErrorCodesEnum::unauthorized
                ], Response::HTTP_UNAUTHORIZED);

            }else{

                // Check Enabled
                if(!$user->enabled){
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/auth.user_disabled'),
                        'error_code' => ErrorCodesEnum::unauthorized
                    ], Response::HTTP_UNAUTHORIZED);
                }

                // Check Store and Branches
                if(GlobalValidation::UserNeedToFilterData(($user))) {

                    $have_sb = count(UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false)) > 0;

                    if (!$have_sb) {
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/auth.no_sb'),
                            'error_code' => ErrorCodesEnum::unauthorized
                        ], Response::HTTP_UNAUTHORIZED);
                    }

                }

                $token = TokenHelper::CreateToken();
                Session::LogSuccessLogin($user, AccessTypeEnum::Api, $token);

            }

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

        // all good so return the token

        $webUrl = Config::get('app.web_url');
        $webPhotoPath = $webUrl . Config::get('app.web_photo_path');
        $webFilePath = $webUrl . Config::get('app.web_file_path');
        $webVideoPath = $webUrl . Config::get('app.web_video_path');

        return response()->json(
            [
                'object' => AppObjectNameEnum::AUTH,
                'token' => $token,
                'user_data' => [
                    'user_id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role_id' => $user->role->role_id,
                    'role_name' => $user->role->name,
                    'role_display_name' => $user->role->display_name
                ],
                'env_data' => [
                    'work_order' => [
                        "web_photo_path" => str_replace ('{module}', 'work_orders', $webPhotoPath),
                        "web_file_path" => str_replace ('{module}', 'work_orders', $webFilePath),
                        "web_video_path" => str_replace ('{module}', 'work_orders', $webVideoPath),
                    ],
                    'work_order_quote' => [
                        "web_photo_path" => str_replace ('{module}', 'work_order_quotes', $webPhotoPath),
                        "web_file_path" => str_replace ('{module}', 'work_order_quotes', $webFilePath),
                    ],
                    'work_order_history' => [
                        "web_photo_path" => str_replace ('{module}', 'work_order_histories', $webPhotoPath),
                        "web_file_path" => str_replace ('{module}', 'work_order_histories', $webFilePath),
                        "web_video_path" => str_replace ('{module}', 'work_order_histories', $webVideoPath),
                    ],
                    'checklist' => [
                        "web_photo_path" => str_replace ('{module}', 'checklists', $webPhotoPath),
                        "web_video_path" => str_replace ('{module}', 'checklists', $webVideoPath),
                    ],
                    'ticket' => [
                        "web_photo_path" => str_replace ('{module}', 'tickets', $webPhotoPath),
                        "web_video_path" => str_replace ('{module}', 'tickets', $webVideoPath),
                    ],
                ]
            ],
            Response::HTTP_OK
        );
    }

    public function Logout(Request $request)
    {
        $token = $request->bearerToken();

        try{
            $session = Session::where('token', $token)
                ->where('status', SessionStateEnum::Abierto)
                ->first();

            if ($session == null) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/auth.invalid_token'),
                    'error_code' => ErrorCodesEnum::not_exists
                ], Response::HTTP_NOT_FOUND);
            }else{
                Session::LogLogout(AccessTypeEnum::Api, $session);
            }

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

        return response(null, Response::HTTP_OK);
    }

    public function Ping(Request $request)
    {
        $token = $request->bearerToken();

        try{
            $session = Session::where('token', $token)
                ->where('status', SessionStateEnum::Abierto)
                ->first();

            if($session == null){
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/auth.invalid_token'),
                    'error_code' => ErrorCodesEnum::not_exists
                ], Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

        return response()->json(
            [
                'object' => AppObjectNameEnum::AUTH,
                'active' => ($session->status == SessionStateEnum::Abierto ? true : false)
            ],
            Response::HTTP_OK
        );
    }

}
