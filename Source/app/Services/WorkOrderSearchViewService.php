<?php

namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\Views\WorkOrderSearch;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class WorkOrderSearchViewService
{
    protected $workOrderSearchView;

    public function __construct(WorkOrderSearch $workOrderSearchView)
    {
        $this->workOrderSearchView = $workOrderSearchView;
    }

    public function findByRole(int $id, User $user){
        return $this->workOrderSearchView->where('id', $id)->filterByRole($user)->first();
    }

    public function search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;

        if($accessType == AccessTypeEnum::Api) {

            $columns = '
                id,
                wo_number,
                branch_id,
                branch_name,
                work_order_status_id,
                work_order_status_name,
                ticket_id,
                ticket_number,
                maintenance_id,
                UNIX_TIMESTAMP(CONVERT_TZ(created_at, \'+00:00\', @@global.time_zone)) as created_at
            ';

            $columns = ltrim(rtrim($columns));
            $query = $this->workOrderSearchView::select(DB::raw($columns));

            $query->with(['wo_contacts' => function ($query) {

                $query->select([
                    'work_order_contact.work_order_id',
                    'user.first_name',
                    'user.last_name',
                    'user.phone'
                ]);
                $query->join('user', 'work_order_contact.user_id', 'user.user_id');

            }]);

        }else if($accessType == AccessTypeEnum::Web) {

            $columns = '*';
            $columns = ltrim(rtrim($columns));
            $query = $this->workOrderSearchView::select(DB::raw($columns));

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

        if ($accessType == AccessTypeEnum::Api) {
            $page = $filterParams['page'];
            $per_page = $filterParams['per_page'];
        }

        PaginateHelper::SetPaginateDefaultValues($page, $per_page);

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->columnsEquivalenceToSearch(), 'wo_number');
        $query->orderBy($sort, $direction);

        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->paginate($per_page);
    }

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'id',
                'equilavence' => 'id'
            ],
            [
                'name' => 'wo_number',
                'equilavence' => 'wo_number'
            ],
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        if (isset($filterParams['status_id']) && !StringHelper::IsNullOrEmptyString($filterParams['status_id'])) {
            $query->where('work_order_status_id', $filterParams['status_id']);
        }

        if (isset($filterParams['branch_id']) && !StringHelper::IsNullOrEmptyString($filterParams['branch_id'])) {
            $query->where('branch_id', $filterParams['branch_id']);
        }

        if (isset($filterParams['ticket_number']) && !StringHelper::IsNullOrEmptyString($filterParams['ticket_number'])) {
            $query->where('ticket_number', $filterParams['ticket_number']);
        }

        if (isset($filterParams['wo_number']) && !StringHelper::IsNullOrEmptyString($filterParams['wo_number'])) {
            $query->where('wo_number', $filterParams['wo_number']);
        }

        $query->filterByRole($filterParams['user']);
    }
}
