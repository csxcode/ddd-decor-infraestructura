<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 6:14 PM
 */

namespace App\Helpers;


use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Services\MailService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class ErrorHelper
{
    public static function SendInternalErrorMessageForApi($e)
    {
        if (App::environment('local')) {
            dd($e);
        } else {
            MailService::SendErrorMail($e, AccessTypeEnum::Api);
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/global.internal_error'),
                'error_code' => ErrorCodesEnum::server_error
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public static function sendResponseNoChanges($resource, $id, $extraData = [])
    {
        $data =  [
            'object' => $resource,
            'id' => $id
        ];

        $lastData = [
            'message' => trans('api/validation.no_changes'),
            'error_code' => ErrorCodesEnum::no_changes
        ];

        $data = array_merge($data, $extraData, $lastData);

        return response()->json($data, Response::HTTP_OK);
    }

}
