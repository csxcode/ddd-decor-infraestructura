<?php namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;

class TokenHelper {

    public static function CreateToken()
    {
        $timestamp = (str_replace('=', '', base64_encode(Carbon::now())));
        $token = Str::random(200).$timestamp;
        return $token;
    }

}