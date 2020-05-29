<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 9:46 AM
 */

namespace Support\Helpers;


use Illuminate\Support\Facades\Config;

class CookieHelper
{
    public static function DeleteCookieToken()
    {
        if (isset($_COOKIE[Config::get('app.cookie_token_name')])) {

            unset($_COOKIE[Config::get('app.cookie_token_name')]);

            // empty value and old timestamp
            setcookie(Config::get('app.cookie_token_name'), '', time() - 3600, '/', FunctionHelper::getDomain());

        }
    }

    public static function GetCookieToken()
    {
        $data = $_COOKIE[Config::get('app.cookie_token_name')];
        return CryptHelper::Decrypt($data);
    }

}
