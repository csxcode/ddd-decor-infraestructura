<?php
namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\WorkOrder\WorkOrderContact;
use App\Models\WorkOrder\WorkOrderCostCenter;

class WorkOrderCostCenterService
{
    public function create($data)
    {
        return WorkOrderCostCenter::create($data);
    }

    public function massCreate($data) : void
    {
        WorkOrderCostCenter::insert($data);
    }

    public function delete(WorkOrderCostCenter $workOrderCostCenter)
    {
        $workOrderCostCenter->delete();
    }

    public function search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;
        $query = null;

        if($accessType == AccessTypeEnum::Api) {

            $columns = '
                work_order_cost_center.work_order_id,
                work_order_cost_center.cost_center_id,
                work_order_cost_center.percent,
                cost_center.code,
                cost_center.name
            ';

            $columns = ltrim(rtrim($columns));
            $query = WorkOrderCostCenter::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

        }

        $query->join('cost_center', 'work_order_cost_center.cost_center_id', 'cost_center.id');


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
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->columnsEquivalenceToSearch(), 'id');
        $query->orderBy($sort, $direction);


        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->paginate($per_page);
    }

    public function getCostCenterExists($workOrderID, $costCenterId, $id = null)
    {
        $query = WorkOrderCostCenter::where('work_order_id', $workOrderID)
            ->where('cost_center_id', $costCenterId);

        if ($id != null)
            $query->where('id', '<>', $id);

        return $query->first();
    }

    public function getByWorkOrderAndCode($workOrderID, $costCenterCode)
    {
        return WorkOrderCostCenter::select('work_order_cost_center.*')
            ->join('cost_center', 'work_order_cost_center.cost_center_id', 'cost_center.id')
            ->where('work_order_cost_center.work_order_id', $workOrderID)
            ->where('cost_center.code', $costCenterCode)
            ->first();
    }

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'id',
                'equilavence' => 'work_order_cost_center.id'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        if (isset($filterParams['work_order_id']) && !StringHelper::IsNullOrEmptyString($filterParams['work_order_id'])) {
            $query->where('work_order_id', $filterParams['work_order_id']);
        }

        $query->filterByRole($filterParams['user']);
    }

}
