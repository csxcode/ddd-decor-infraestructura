<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 2:54 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserStoreBranch extends Model
{
    protected $table = 'user_store_branch';
    protected $fillable = ['user_id', 'branch_id'];

    public static function GetStoreBranchIdsByUser($user_id, $inline = true){
        if($inline){
            return UserStoreBranch::where('user_id', $user_id)->pluck('branch_id')->implode(',');
        }else{
            return UserStoreBranch::where('user_id', $user_id)->pluck('branch_id')->all();
        }
    }

    public static function GetUsersEmailByRoleAndBranchRelated($role_id, $branch_id){
        $query = "
          select
            user.email
          from
            user inner join user_store_branch on user.user_id = user_store_branch.user_id
          where
            ifnull(ltrim(rtrim(user.email)), '') <> '' and
            user.role_id = :role_id and
            user_store_branch.branch_id = :branch_id and
            user.enabled = 1
        group by
            user.email;
        ";

        $emails = DB::select($query, [
            'role_id' => $role_id,
            'branch_id' => $branch_id
        ]);

        return $emails;
    }


    public static function GetUsersEmailByRole($role_id){

        $query = "
            select
                user.email
            from
                user
            where
                ifnull(ltrim(rtrim(user.email)), '') <> '' and
                user.role_id = :role_id and
                user.enabled = 1
            group by
                user.email;
        ";

      $emails = DB::select($query, [
          'role_id' => $role_id
      ]);

      return $emails;
    }

}
