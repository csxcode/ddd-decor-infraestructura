<?php

namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderHistory;
use App\UseCases\WorkOrderHistory\NotifyEmailByStatusChange;
use Illuminate\Support\Facades\DB;

class WorkOrderHistoryService
{
    private $workOrderSearch;
    private $workOrderService;

    public function __construct(WorkOrderSearch $workOrderSearch, WorkOrderService $workOrderService)
    {
        $this->workOrderSearch = $workOrderSearch;
        $this->workOrderService = $workOrderService;
    }

    public function findByWorkOrderAndRole(int $id, int $workOrderId, User $user)
    {
        // select
        $columns = '
            work_order_history.*,
            work_order_status.name as status_name
        ';

        $columns = ltrim(rtrim($columns));
        $query = WorkOrderHistory::select(DB::raw($columns));

        // joins
        $query->join('work_order_status', 'work_order_history.work_order_status_id', 'work_order_status.id');
        $query->join('v_work_order_search', 'work_order_history.work_order_id', 'v_work_order_search.id');

        // filter by id
        $query->where('work_order_history.id', $id);
        $query->where('work_order_history.work_order_id', $workOrderId);

        // filter by rule
        $this->workOrderSearch->scopeFilterByRole($query, $user);

        return $query->first();
    }

    public function findByWorkOrder(int $id, $workOrderId)
    {
        $query = WorkOrderHistory::where('id', $id)->where('work_order_id', $workOrderId);
        return $query->first();
    }

    public function store($data, $user) : WorkOrderHistory
    {
        $oldWorkOrder = WorkOrder::findOrFail($data['work_order_id']);
        $workOrderHistory = null;

        $this->syncWorkOrderByHistory($data, $user);
        $workOrderHistory = $this->create($data, $user);

        # Notification
        (new NotifyEmailByStatusChange())->run($workOrderHistory, $oldWorkOrder);

        return $workOrderHistory;
    }

    /**
     * Sync WorkOrder (replicate some field)
     * @param $data
     * @param $user
     */
    private function syncWorkOrderByHistory($data, $user) : void
    {
        $workOrder = WorkOrder::findOrFail($data['work_order_id']);

        $start_date = (isset($data['start_date']) ? StringHelper::Trim($data['start_date']): "");
        if(!StringHelper::IsNullOrEmptyString($start_date))
            $workOrder->start_date = $start_date;

        $end_date = (isset($data['end_date']) ? StringHelper::Trim($data['end_date']): "");
        if(!StringHelper::IsNullOrEmptyString($end_date))
            $workOrder->end_date = $end_date;

        $workOrder->work_order_status_id = $data['work_order_status_id'];

        $this->workOrderService->update($workOrder, $user);
    }

    public function create($data, $user) : WorkOrderHistory
    {
        $data['created_at'] = now();
        $data['created_by_user'] = User::GetCreatedByUser($user);
        $data['updated_at'] = null;

        return WorkOrderHistory::create($data);
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
                work_order_history.id,
                UNIX_TIMESTAMP(CONVERT_TZ(work_order_history.created_at, \'+00:00\', @@global.time_zone)) as created_at,
                work_order_history.created_by_user,
                work_order_status.id as work_order_status_id,
                work_order_status.name as work_order_status_name
            ';

            $columns = ltrim(rtrim($columns));
            $query = WorkOrderHistory::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

        }

        $query->join('work_order_status', 'work_order_history.work_order_status_id', 'work_order_status.id');
        $query->join('v_work_order_search', 'work_order_history.work_order_id', 'v_work_order_search.id');


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

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'id',
                'equilavence' => 'work_order_history.id'
            ]
        );
    }

    private function setFilterForSearch($filterParams, $accessType, &$query)
    {
        if (isset($filterParams['work_order_id']) && !StringHelper::IsNullOrEmptyString($filterParams['work_order_id'])) {
            $query->where('work_order_id', $filterParams['work_order_id']);
        }

        $this->workOrderSearch->scopeFilterByRole($query, $filterParams['user']);
    }


}
