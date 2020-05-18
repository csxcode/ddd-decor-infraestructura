<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 6:14 PM
 */

namespace Support\Helpers;


use Support\Enums\AccessTypeEnum;
use Support\Enums\AppObjectNameEnum;
use Support\Enums\ErrorCodesEnum;
use Support\Services\MailService;
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

    public static function sendResponseNoChanges($resource, $id)
    {
        return response()->json(
            [
                'object' => $resource,
                'id' => $id,
                'message' => trans('api/validation.no_changes'),
                'error_code' => ErrorCodesEnum::no_changes
            ],
            Response::HTTP_OK
        );
    }

}
