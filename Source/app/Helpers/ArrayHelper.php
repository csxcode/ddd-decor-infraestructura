<?php namespace App\Helpers;

class ArrayHelper
{
    public static function checkValueExistsInAssociativeArray($array, $key, $val)
    {
        foreach ($array as $item)
            if (isset($item[$key]) && $item[$key] == $val)
                return true;
        return false;
    }

    public static function setNullToEmptyValue($val)
    {
        return (empty($val) ? NULL : $val);
    }

    public static function convertValidDateTime($val)
    {
        if(empty($val)){
            return NULL;
        }else{
            return date("Y-m-d H:i:s", strtotime($val));
        }
    }

    public static function sortmulti($array, $index, $order, $natsort=FALSE, $case_sensitive=FALSE) {
        $sorted = null;
        if(is_array($array) && count($array)>0) {
            foreach(array_keys($array) as $key)
                $temp[$key]=$array[$key][$index];
            if(!$natsort) {
                if ($order=='asc')
                    asort($temp);
                else
                    arsort($temp);
            }
            else
            {
                if ($case_sensitive===true)
                    natsort($temp);
                else
                    natcasesort($temp);
                if($order!='asc')
                    $temp=array_reverse($temp,TRUE);
            }
            foreach(array_keys($temp) as $key)
                if (is_numeric($key))
                    $sorted[]=$array[$key];
                else
                    $sorted[$key]=$array[$key];
            return $sorted;
        }
        return $sorted;
    }

   public static function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr = array_reverse($arr, true);
        $arr[$key] = $val;
        return array_reverse($arr, true);
    }

    public static function array_search_multidimensional($array, $keyToSearchValue, $searchValue, $returnValue) {
        foreach ($array as $id => $item) {
            if ($item[$keyToSearchValue] === $searchValue) {
                return $item[$returnValue];
            }
        }
        return null;
    }

    public static function convertListIdsFromStringToInteger($ids){

        $array = explode('/', str_replace(' ', '', $ids));

        foreach($array as $index => $item){
            $array[$index] = strval((int)$item);
        }

        return $array;
    }

    public static function search($array, $key, $value)
    {
        $results = array();


        if (is_array($array))
        {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray) {
                $results = array_merge($results, self::search($subarray, $key, $value));
            }
        }

        return $results;
    }

    public static function removeElementWithValue($array, $key, $value){
        foreach($array as $subKey => $subArray){
             if($subArray[$key] == $value){
                  unset($array[$subKey]);
             }
        }
        return $array;
   }
}

