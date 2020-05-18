<?php namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class LinkHelper
{
    public static function getLinkForSortByStores($column, $text)
    {
        $direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
        $route = Route::currentRouteName();

        $link = link_to_route($route, '{0}',
            [
                'store_name' => Request::get('store_name'),
                'branch_name' => Request::get('branch_name'),
                'status' => Request::get('status'),
                'sort' => $column,
                'direction' => $direction,
                'page' => Request::get('page')
            ],
            [
                'class' => 'table-header-link'
            ]
        );

        return self::createGlyphIconForLink($column, $text, $link);
    }

    public static function getLinkForSortByUsers($column, $text)
    {
        $direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
        $route = Route::currentRouteName();

        $link = link_to_route($route, '{0}',
            [
                'name' => Request::get('name'),
                'role' => Request::get('role'),
                'enabled' => Request::get('enabled'),
                'sort' => $column,
                'direction' => $direction,
                'page' => Request::get('page')
            ],
            [
                'class' => 'table-header-link'
            ]
        );

        return self::createGlyphIconForLink($column, $text, $link);
    }

    public static function getLinkForSortBySessions($column, $text)
    {
        $direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
        $route = Route::currentRouteName();

        $link = link_to_route($route, '{0}',
            [
                'name' => Request::get('name'),
                'access_type' => Request::get('access_type'),
                'status' => Request::get('status'),
                'login_from' => Request::get('login_from'),
                'login_to' => Request::get('login_to'),
                'last_activity_from' => Request::get('last_activity_from'),
                'last_activity_to' => Request::get('last_activity_to'),
                'logout_from' => Request::get('logout_from'),
                'logout_to' => Request::get('logout_to'),
                'ip_address' => Request::get('ip_address'),
                'sort' => $column,
                'direction' => $direction,
                'page' => Request::get('page')
            ],
            [
                'class' => 'table-header-link'
            ]
        );

        return self::createGlyphIconForLink($column, $text, $link);
    }

    public static function getLinkForSortByChecklists($column, $text)
    {
        $direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
        $route = Route::currentRouteName();

        $link = link_to_route($route, '{0}',
            [
                'checklist_number' => Request::get('checklist_number'),
                'branch' => Request::get('branch'),                                
                'date_from' => Request::get('date_from'),
                'date_to' => Request::get('date_to'),
                'status' => Request::get('status'),
                'sort' => $column,
                'direction' => $direction,
                'page' => Request::get('page')
            ],
            [
                'class' => 'table-header-link'
            ]
        );

        return self::createGlyphIconForLink($column, $text, $link);
    }

    public static function getLinkForSortByTickets($column, $text)
    {
        $direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
        $route = Route::currentRouteName();

        $link = link_to_route($route, '{0}',
            [
                'ticket_number' => Request::get('ticket_number'),
                'store' => Request::get('store'),
                'branch' => Request::get('branch'),
                'type' => Request::get('type'),
                'date_from' => Request::get('date_from'),
                'date_to' => Request::get('date_to'),
                'status' => Request::get('status'),
                'sort' => $column,
                'direction' => $direction,
                'page' => Request::get('page')
            ],
            [
                'class' => 'table-header-link'
            ]
        );

        return self::createGlyphIconForLink($column, $text, $link);
    }

    public static function getLinkForSortByExhibitions($column, $text)
    {
        $direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
        $route = Route::currentRouteName();

        $link = link_to_route($route, '{0}',
            [
                'name' => Request::get('name'),
                'store' => Request::get('store'),
                'branch' => Request::get('branch'),
                'type' => Request::get('type'),
                'enabled' => Request::get('enabled'),
                'pending_to_check' => Request::get('pending_to_check'),
                'sort' => $column,
                'direction' => $direction,
                'page' => Request::get('page')
            ],
            [
                'class' => 'table-header-link'
            ]
        );

        return self::createGlyphIconForLink($column, $text, $link);
    }

    public static function createGlyphIconForLink($column, $text, $link)
    {
        $glyphicon = 'fa fa-sort';

        if (!is_null(Request::get('direction')) and ($column == Request::get('sort'))) {
            $glyphicon = 'fa ' . ((Request::get('direction') == 'asc') ? 'fa-sort-up' : 'fa-sort-down');
        }

        $span = str_replace('{0}', $glyphicon, ('<i class="{0}" aria-hidden="true"></i>&nbsp;' . $text));

        return str_replace('{0}', $span, $link);
    }

    public static function SetActiveRoute($controllers, $action = null, $root = false)
    {
        $return = "";
        $uri = \Route::getFacadeRoot()->current()->uri();

        foreach ($controllers as $c){
            if($root){
                if(StringHelper::IsNullOrEmptyString($action)){
                    $i = strpos($uri,'/');
                    if($i != false){
                        $action = (substr($uri, ($i +1), strlen($uri)));
                    }
                }
            }

            $route = $c.(StringHelper::IsNullOrEmptyString($action) ? "" : "/".$action);

            if($uri == $route){
                $return = 'active';
                break;
            }
        }

        return $return;
    }

    public static function GetUrlPrevious(){
        $current_url = url()->current();
        $previous_url = URL::previous();
        $return = $previous_url;

        // Set default url (main) to avoid problems
        if($current_url == $previous_url){
            $return = '/';
        }

        return $return;

    }

    public static function GetUrlExportExcelExhibitions()
    {
        $route = 'exhibitions.export';

        return route($route, array(
            'name' => Request::get('name'),
            'store' => Request::get('store'),
            'branch' => Request::get('branch'),
            'type' => Request::get('type'),
            'enabled' => Request::get('enabled'),
            'pending_to_check' => Request::get('pending_to_check'),
            'sort' => Request::get('sort'),
            'direction' => Request::get('direction')
        ));
    }

    public static function GetUrlExportExcelChecklist()
    {
        $route = 'checklist.export';

        return route($route, array(
            'checklist_number' => Request::get('checklist_number'),
            'branch' => Request::get('branch'),            
            'date_from' => Request::get('date_from'),
            'date_to' => Request::get('date_to'),
            'status' => Request::get('status'),
            'sort' => Request::get('sort'),
            'direction' => Request::get('direction')
        ));
    }

    public static function GetUrlExportExcelTickets()
    {
        $route = 'tickets.export';

        return route($route, array(
            'ticket_number' => Request::get('ticket_number'),
            'store' => Request::get('store'),
            'branch' => Request::get('branch'),
            'type' => Request::get('type'),
            'date_from' => Request::get('date_from'),
            'date_to' => Request::get('date_to'),
            'status' => Request::get('status'),
            'sort' => Request::get('sort'),
            'direction' => Request::get('direction')
        ));
    }   
    
}


