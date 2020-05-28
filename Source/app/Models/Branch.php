<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 2:54 PM
 */

namespace App\Models;


use App\Http\Controllers\Api\Validations\GlobalValidation;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
    protected $primaryKey = 'branch_id';
    protected $hidden = ['store_id'];
    protected $fillable = [
        'name', 'enabled', 'store_id', 'created_at', 'updated_at'
    ];

    public function store() {
        return $this->hasOne(Store::class, 'store_id', 'store_id');
    }

    public function branch_locations() {
        return $this->hasMany(BranchLocation::class, 'branch_branch_id', 'branch_id')->orderBy('name');
    }

    public static function GetUserBranches($user_id, $store_id){

        $user = User::with('role')->find($user_id);

        $query = Branch::select('branch.branch_id', 'branch.name as branch_name')
            ->leftJoin('store', 'branch.store_id', 'store.store_id')
            ->where('branch.store_id', $store_id);


        // Filter only user data related
        if(GlobalValidation::UserNeedToFilterData($user)){
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);

            // Filter only store related
            $query->where(function ($query) use ($usb_allowed) {
                $query->whereIn('branch.branch_id', $usb_allowed);
            });

        }

        return $query->orderBy('branch.name', 'asc')->groupBy('branch.branch_id', 'branch.name')->get();
    }

}
