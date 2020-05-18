<?php namespace App\Http\Middleware;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\SessionStateEnum;
use App\Models\Session;
use App\Models\User;
use Closure;
use Illuminate\Http\Response;

class AuthApi {

    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if($token == null){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/auth.empty_token'),
                'error_code' => ErrorCodesEnum::unauthorized
            ], Response::HTTP_UNAUTHORIZED);
        }else{
            $api_token = Session::where('token', $token)
                ->where('status', SessionStateEnum::Abierto)
                ->first();

            if ($api_token == null) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/auth.invalid_token'),
                    'error_code' => ErrorCodesEnum::unauthorized
                ], Response::HTTP_UNAUTHORIZED);
            }else{
                $user = User::where('user_id', $api_token->user_id)->first();
                if($user == null){
                    return response()->json([
                        'object' => AppObjectNameEnum::ERROR,
                        'message' => trans('api/auth.user_not_found'),
                        'error_code' => ErrorCodesEnum::not_exists
                    ], Response::HTTP_NOT_FOUND);
                }else{
                    if(!$user['enabled']){
                        return response()->json([
                            'object' => AppObjectNameEnum::ERROR,
                            'message' => trans('api/auth.user_disabled'),
                            'error_code' => ErrorCodesEnum::unauthorized
                        ], Response::HTTP_UNAUTHORIZED);
                    }
                }
            }
        }

        Session::TrackingLastActivity(AccessTypeEnum::Api, $token);

        return $next($request);
    }
}