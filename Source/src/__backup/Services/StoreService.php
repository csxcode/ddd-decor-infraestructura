<?php

namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Store;

class StoreService
{

    private $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function search($accessType, $filterParams = null, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;
        $query = null;

        if($accessType == AccessTypeEnum::Api) {

            $query = Store::select('store.store_id', 'store.name', 'store.enabled');
            $query->leftJoin('branch', 'store.store_id', 'branch.store_id');

            $query->with(['branches' => function($q) use ($filterParams) {

                $q->with(['branch_locations' => function($q) use ($filterParams) {

                    if (isset($filterParams['active']) && !StringHelper::IsNullOrEmptyString($filterParams['active'])) {
                        $q->where('enabled', $filterParams['active']);
                    }

                }]);

                $this->store->scopeFilterByRole($q, $filterParams['user']);

                if (isset($filterParams['active']) && !StringHelper::IsNullOrEmptyString($filterParams['active'])) {
                    $q->where('enabled', $filterParams['active']);
                }
            }]);

        }

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        $this->setFilterForSearch($filterParams, $accessType, $query);


        // -------------------------------------
        // Set Paginate
        // -------------------------------------
        $page = null;
        $per_page = null;

        PaginateHelper::SetPaginateDefaultValues($page, $per_page);

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->columnsEquivalenceToSearch(), 'id');
        $query->orderBy($sort, $direction);


        // -------------------------------------
        // Return Data
        // -------------------------------------
        if($accessType == AccessTypeEnum::Api)
            return $query->groupBy('store.store_id', 'store.name', 'store.enabled')->get();
        else
            return $query->paginate($per_page);
    }

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'id',
                'equilavence' => 'store.store_id'
            ],
            [
                'name' => 'name',
                'equilavence' => 'store.name'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        if (isset($filterParams['active']) && !StringHelper::IsNullOrEmptyString($filterParams['active'])) {
            $query->where('store.enabled', $filterParams['active']);
        }

        $query->filterByRole($filterParams['user']);
    }

}
