<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 6/13/2019
 * Time: 3:27 PM
 */

namespace App\Helpers;

use Carbon\Carbon;

class DatetimeHelper
{
    public static function GetDateTimeByTimeZone($value, $fromFormat = null, $toFormat = null){

        $return = null;

        if($value) {

            if(strtotime($value)) {

                // values by default
                $tz = new \DateTimeZone('America/Lima');
                $default_format = 'Y-m-d H:i:s';

                // formats by default or custom
                $fromFormat = ($fromFormat == null ? $default_format : $fromFormat);
                $toFormat = ($toFormat == null ? $default_format : $toFormat);

                // create datetime from format
                $date = \DateTime::createFromFormat($fromFormat, $value);

                // set timezone
                $date->setTimezone($tz);

                // set formart to return
                $return = $date->format($toFormat);
            }
        }

        return $return;
    }

    public static function TransformToTimeStamp($datetime){
        return ($datetime == null ? null : Carbon::parse($datetime)->timestamp);
    }
}