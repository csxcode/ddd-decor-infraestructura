<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 2:53 PM
 */

namespace App\Models;


use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Store extends Model
{
    protected $table = 'store';
    protected $primaryKey = 'store_id';
    protected $fillable = [
        'name', 'enabled', 'created_at', 'updated_at'
    ];

    public function branches() {
        return $this->hasMany(Branch::class, 'store_id', 'store_id')->orderBy('name');
    }

    public static function GetUserStores($user_id){

        $user = User::with('role')->find($user_id);

        $query = Store::select('store.store_id', 'store.name as store_name')
            ->leftJoin('branch', 'store.store_id', 'branch.store_id');


        // Filter only user data related
        if(GlobalValidation::UserNeedToFilterData($user)){
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);

            // Filter only store related
            $query->where(function ($query) use ($usb_allowed) {
                $query->whereIn('branch.branch_id', $usb_allowed);
            });

        }

        return $query->orderBy('store.name', 'asc')->groupBy('store.store_id', 'store.name')->get();
    }

    public static function Search($filterParams, $sortByParams)
    {
        $query = Store::select('store.store_id', 'store.name as name', 'store.enabled as status')
            ->leftJoin('branch', 'store.store_id', 'branch.store_id');

        //Set Filters
        if (!StringHelper::IsNullOrEmptyString($filterParams['store_name'])) {
            $query->where('store.name', 'like', '%'.$filterParams['store_name'].'%');
        }

        if (!StringHelper::IsNullOrEmptyString($filterParams['branch_name'])) {
            $query->where('branch.name', 'like', '%'.$filterParams['branch_name'].'%');
        }

        if (!StringHelper::IsNullOrEmptyString($filterParams['status'])) {
            $query->where('store.enabled', '=', $filterParams['status']);
        }

        //Set Order by
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = isset($sortByParams['direction']) ? $sortByParams['direction'] : 'asc';
        $sort = 'name'; //use default sort by users

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];

            if($sort == 'enabled'){
                $sort = 'store.enabled';
            }
        }

        $query->groupBy(['store.store_id', 'store.name', 'store.enabled']);
        $query->orderBy($sort, $direction);

        //Get data and return
        return $query->paginate(Config::get('app.paginate_default_per_page'));
    }

    private static function columnsAllowedForSortBy(){
        return [
            'name',
            'enabled',
        ];
    }

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================
    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::ADMIN || $role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            $all = true;

        } else if ($role == UserRoleEnum::RESPONSABLE_SEDE) {

            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            $query->whereIn('branch_id', $usb_allowed);

        } else if ($role == UserRoleEnum::PROVEEDOR) {

            $query->where('store.store_id', 0);

        }
    }

}
