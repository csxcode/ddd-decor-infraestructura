<?php namespace Support\Helpers;


class CryptHelper
{
    public static function Encrypt($value)
    {
        $encrypted_value = base64_encode(
            /*mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                md5(env('APP_KEY')),
                $value,
                MCRYPT_MODE_CBC,
                md5(md5(env('APP_KEY')))
            )*/

            openssl_encrypt($value, 'bf-ecb', md5(env('APP_KEY')), true)

        );


        return $encrypted_value;
    }

    public static function Decrypt($value)
    {
        $return = rtrim(
            /*mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                md5(env('APP_KEY')),
                base64_decode($value),
                MCRYPT_MODE_CBC,
                md5(md5(env('APP_KEY')))
            )*/
            openssl_decrypt($value, 'bf-ecb', md5(env('APP_KEY')), true)
            , "\0"
    );
        return $return;
    }

}
