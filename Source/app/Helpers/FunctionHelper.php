<?php namespace App\Helpers;

use App\Enums\CurrencyEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FunctionHelper
{
    // Function to get the client IP address
    public static function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public static function getDate($date, $formatIn = 'Y-m-d', $FormatOut = 'd/m/Y'){
        $ret = "";
        $d = \DateTime::createFromFormat($formatIn, $date);

        if ($d && $d->format($formatIn) == $date) {
            $ret = $d->format($FormatOut);
        }

        return $ret;
    }

    public static function getTimeRange($TimeFrom, $TimeTo){
        $ret = "";

        if(isset($TimeFrom) and trim($TimeFrom) != '' and isset($TimeTo) and trim($TimeTo) != ''){
            $f = \DateTime::createFromFormat('H:i:s', $TimeFrom);
            $t = \DateTime::createFromFormat('H:i:s', $TimeTo);

            $ret = $ret.' '.$f->format('h:i a').' - '.$t->format('h:i a');
        }

        return $ret;
    }

    public static function getFieldShort($text, $len){
        return (strlen($text) > $len ? substr($text, 0, $len).'...' : $text);
    }

    public static function checkIfUrlContainsParameters(){
        return strpos($_SERVER['REQUEST_URI'], '?');
    }

    public static function getDomain(){
        return ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
    }

    function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array))
        {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, search($subarray, $key, $value));
        }

        return $results;
    }

    public static function isValidEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function CreateGUID($length = 100)
    {
        $timestamp = (str_replace('=', '', base64_encode(str_shuffle(Carbon::now()->timestamp))));
        $guid = Str::random($length).$timestamp;
        return $guid;
    }

    public static function validateDecimals($value, $numberOfDecimals)
    {
        if(preg_match('/^[0-9]+\.[0-9]{'.$numberOfDecimals.'}$/', $value))
            return true;
        else
            return false;
    }

    public static function createFolder($path) :void
    {
        // Check if the folder exists, in case does not exists will be create
        $folder_path = $path;
        if (!file_exists($folder_path)) {
            mkdir($folder_path, 0777, true);
        }
    }

    public static function deleteFile($path) : void
    {
        if(file_exists($path)){
            \File::delete($path);
        }
    }

    public static function getCurrencyName($currency)
    {
        $return = null;

        if($currency == CurrencyEnum::Soles)
            $return = 'PEN';

        if($currency == CurrencyEnum::Dolares)
            $return = 'USD';

        return $return;
    }

}
