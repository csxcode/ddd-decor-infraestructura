<?php

namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\User;

class ContactService
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
                user.user_id as id,
                user.first_name,
                user.last_name,
                user.username,
                user.email,
                user.phone
            ';

            $columns = ltrim(rtrim($columns));
            $query = User::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

        }

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        $query->where('contact_enabled', 1);
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
        $query->orderBy(\DB::raw($sort), $direction);


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
                'equilavence' => 'user.user_id'
            ],
            [
                'name' => 'name',
                'equilavence' => 'concat(first_name, last_name)'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        $query->filterByRoleForContacts($filterParams['user']);
    }


}
