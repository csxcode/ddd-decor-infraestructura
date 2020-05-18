<?php

namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\MajorAccount;

class MajorAccountService
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
                major_account.id,
                major_account.code,
                major_account.name
            ';

            $columns = ltrim(rtrim($columns));
            $query = MajorAccount::select(\DB::raw($columns));

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
                'equilavence' => 'major_account.id'
            ],
            [
                'name' => 'code',
                'equilavence' => 'major_account.code'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        $query->filterByRole($filterParams['user']);
    }


}
