<?php

namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Vendor;

class VendorService
{
    public function search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;
        $query = null;

        if($accessType == AccessTypeEnum::Api) {

            $columns = '
                vendor.id,
                vendor.name,
                vendor.phone,
                vendor.email,
                vendor.contact_name,
                vendor.vendor_status
            ';

            $columns = ltrim(rtrim($columns));
            $query = Vendor::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

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
            return $query->get();
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
                'equilavence' => 'vendor.id'
            ],
            [
                'name' => 'name',
                'equilavence' => 'vendor.name'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        if (isset($filterParams['vendor_status']) && !StringHelper::IsNullOrEmptyString($filterParams['vendor_status'])) {
            $query->where('vendor_status', $filterParams['vendor_status']);
        }

        $query->filterByRole($filterParams['user']);
    }


}
