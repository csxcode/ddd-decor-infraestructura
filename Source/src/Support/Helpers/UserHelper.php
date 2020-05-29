<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 6/13/2019
 * Time: 10:01 AM
 */

namespace Support\Helpers;


use Support\Enums\UserRoleEnum;

class UserHelper
{
    public static function CheckUserTypeCanSeeStoresAndBranches($role){

        $return = false;
        $roles_allowed = explode(',', UserRoleEnum::ROLES_IDS_ALLOWED_TO_ADD_SB);
        foreach ($roles_allowed as $role_id) {
          if($role_id == $role){
              $return = true;
              break;
          }
        }

        return $return;
    }

    public static function ExtractUserNameFromString($value){

        $return = null;

        $value = strtolower($value);
        $start_parenthesis = strpos($value, '(');
        $end_parenthesis = strpos($value, ')');
        $len_to_extract = ($end_parenthesis - $start_parenthesis);

        if($start_parenthesis != false && $end_parenthesis != false){
            if($end_parenthesis > $start_parenthesis){
                $return = substr($value, ($start_parenthesis + 1), ($len_to_extract - 1));
            }
        }

        return $return;
    }

}
