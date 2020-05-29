<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 5:14 AM
 */

namespace Support\Helpers;


class StringHelper
{
    public static function IsNullOrEmptyString($str){
        return (!isset($str) || trim($str) === '');
    }

    public static function Trim($value){
        return ltrim(rtrim($value));
    }

    public static function GetEnabledFormat($value){
        $return = "";
        if(!self::IsNullOrEmptyString($value)){
            $value = self::Trim($value);
            $return = ($value == 1 ? "Activo" : "Inactivo");
        }
        return $return;
    }

    public static function GetConditionalFormat($value){
        $return = "";
        if(!self::IsNullOrEmptyString($value)){
            $value = self::Trim($value);
            $return = ($value == 1 ? "Si" : "No");
        }
        return $return;
    }


    public static function SubString($value, $len){
        $three_points = '';

        if(strlen($value) > $len){
            $three_points = '...';
        }

        return substr($value, 0, $len) . $three_points;
    }


}
