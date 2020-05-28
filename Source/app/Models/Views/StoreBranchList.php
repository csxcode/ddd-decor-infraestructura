<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/24/2019
 * Time: 9:56 AM
 */

namespace App\Models\Views;


use App\Models\UserStoreBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class StoreBranchList extends Model
{
    protected $table = 'v_store_branch_list';

    public static function GetAllByUser($user_id){
        $return = [];
        $branches_selected = UserStoreBranch::GetStoreBranchIdsByUser($user_id);
        $sb_list = StoreBranchList::where('store_id', Config::get('app.decorcenter_store_id'))->get();
        
        $grouped = $sb_list->groupBy('store_id')->toArray();
        $branches_selected = explode(',', $branches_selected);

        foreach ($grouped as $key=>$branches){

            $store = $sb_list->filter(function($item) use ($key) {
                return $item->store_id == $key;
            })->first();


            $counter = array_filter($branches, function($item) use ($branches_selected) {
                return in_array($item['branch_id'], $branches_selected);
            });

            $branches = array_map(function (array $item) use ($branches_selected) {
                $selected = in_array($item['branch_id'], $branches_selected);
                $item['selected'] = $selected;
                return $item;
            }, $branches);

            $array = [
                'store_id' => $key,
                'store_name' => $store->store_name,
                'counter' => count($counter),
                'branches' => $branches
            ];
            array_push($return, $array);
        }

        return $return;
    }
}