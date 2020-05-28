<?php

namespace App\UseCases\Maintenance;

use App\Contracts\ISearchAccessType;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Maintenance\Maintenance;
use Carbon\Carbon;

class Search implements ISearchAccessType
{
    public function search($filterParams, $sortByParams = null)
    {
        $columns = '
            maintenance.id,
            maintenance.reminder1,
            maintenance.maintenance_number,
            maintenance.maintenance_title,
            maintenance.maintenance_date,

            maintenance_status.id as status_id,
            maintenance_status.name as status_name,

            branch_location.name as branch_location_name,
            branch.branch_id,
            branch.name as branch_name,

            work_order.id as work_order_id,
            work_order.wo_number
        ';

        $columns = ltrim(rtrim($columns));

        $query = Maintenance::select(\DB::raw($columns))
            ->leftJoin('maintenance_status', 'maintenance.status_id', 'maintenance_status.id')
            ->leftJoin('work_order', 'maintenance.id', 'work_order.maintenance_id')
            ->leftJoin('branch_location', 'maintenance.branch_location_id', 'branch_location.id')
            ->leftJoin('branch', 'branch_location.branch_branch_id', 'branch.branch_id');

        $this->setFilters($filterParams, $query);

        // Set Paginate
        $per_page = $filterParams['per_page'];
        PaginateHelper::SetPaginateDefaultValues($page, $per_page);

        // Set OrderBy
        $sortByDefault = 'maintenance.id';
        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->equilaventColumns(), $sortByDefault);
        $query->orderBy($sort, $direction);

        // Return Data
        return $query->paginate($per_page);
    }

    private function equilaventColumns()
    {
        return array(
            [
                'name' => 'id',
                'equilavence' => 'maintenance.id'
            ]
        );
    }

    private function setFilters($filterParams, &$query)
    {
        if (isset($filterParams['status_id']) && !StringHelper::IsNullOrEmptyString($filterParams['status_id'])) {
            $query->where('maintenance.status_id', $filterParams['status_id']);
        }

        if (isset($filterParams['branch_id']) && !StringHelper::IsNullOrEmptyString($filterParams['branch_id'])) {
            $query->where('branch.branch_id', $filterParams['branch_id']);
        }

        if (isset($filterParams['date_from']) && !StringHelper::IsNullOrEmptyString($filterParams['date_from'])) {
            $date_from = Carbon::createFromTimestamp($filterParams['date_from'])->toDateString();
            $date_from = date('Y-m-d 00:00:00', strtotime($date_from));
            $query->where('maintenance.maintenance_date', '>=', $date_from);
        }

        if (isset($filterParams['date_to']) && !StringHelper::IsNullOrEmptyString($filterParams['date_to'])) {
            $date_to = Carbon::createFromTimestamp($filterParams['date_to'])->toDateString();
            $date_to = date('Y-m-d 23:59:59', strtotime($date_to));
            $query->where('maintenance.maintenance_date', '<=', $date_to);
        }

        $maintenanceModel = new Maintenance();
        $maintenanceModel->scopeFilterByRole($query, $filterParams['user']);
    }
}
