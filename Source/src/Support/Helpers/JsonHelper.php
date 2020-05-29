<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/17/2019
 * Time: 7:25 AM
 */

namespace Support\Helpers;


use Support\Enums\AppObjectNameEnum;
use Support\Enums\ErrorCodesEnum;
use Illuminate\Http\Response;

class JsonHelper
{
    public static function ReturnResponseJsonInValid(){
        return response()->json([
            'object' => AppObjectNameEnum::ERROR,
            'message' => trans('api/global.json_not_valid'),
            'error_code' => ErrorCodesEnum::already_exists
        ], Response::HTTP_BAD_REQUEST);
    }
}
