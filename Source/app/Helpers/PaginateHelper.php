<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/16/2019
 * Time: 11:21 AM
 */

namespace App\Helpers;


use Illuminate\Support\Facades\Config;

class PaginateHelper
{
    public static function SetPaginateDefaultValues(&$page, &$per_page)
    {
        $page = StringHelper::IsNullOrEmptyString($page) ? Config::get('app.paginate_default_page') : $page;

        $per_page = StringHelper::IsNullOrEmptyString($per_page) ? Config::get('app.paginate_default_per_page') : $per_page;

        // if it exceeds the limit then set default value
        $per_page = $per_page > Config::get('app.paginate_limit') ? Config::get('app.paginate_limit') : $per_page;
    }

    public static function TransformPaginateData($object_name, $paginate_name, $data)
    {
        return [
            'object' => $object_name,
            'total_count' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            $paginate_name => $data->toArray()['data']
        ];
    }

    public static function getEquivalenceSort($name, $equivalences, $sortNameByDefault)
    {
        $name = StringHelper::Trim($name);

        $return = ArrayHelper::array_search_multidimensional($equivalences, 'name', $name, 'equilavence');

        if($return == null)
            $return = ArrayHelper::array_search_multidimensional($equivalences, 'name', $sortNameByDefault, 'equilavence');

        if(!$return)
            $return = $sortNameByDefault;

        return $return;
    }

}
