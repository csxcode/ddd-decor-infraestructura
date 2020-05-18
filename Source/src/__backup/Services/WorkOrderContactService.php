<?php
namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Helpers\ArrayHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderContact;

class WorkOrderContactService
{
    protected $workOrderSearch;

    public function __construct(WorkOrderSearch $workOrderSearch)
    {
        $this->workOrderSearch = $workOrderSearch;
    }

    public function getContactUsersByWorkOrder(int $workOrderId)
    {
        return WorkOrderContact
            ::join('user', 'work_order_contact.user_id', 'user.user_id')
            ->where('work_order_id', $workOrderId)
            ->get();
    }

    public function create($data)
    {
        return WorkOrderContact::create($data);
    }

    public function massCreate($data) : void
    {
        WorkOrderContact::insert($data);
    }

    public function delete(WorkOrderContact $workOrderContact)
    {
        $workOrderContact->delete();
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
                user.user_id,
                user.first_name,
                user.last_name,
                user.username,
                user.email,
                user.phone
            ';

            $columns = ltrim(rtrim($columns));
            $query = WorkOrderContact::select(\DB::raw($columns));

        } else if($accessType == AccessTypeEnum::Web) {

            $columns = '*';
            $columns = ltrim(rtrim($columns));
            $query = WorkOrderContact::select(\DB::raw($columns));
        }

        $query->join('user', 'work_order_contact.user_id', 'user.user_id');
        $query->join('v_work_order_search', 'work_order_contact.work_order_id', 'v_work_order_search.id');


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
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->columnsEquivalenceToSearch(), 'woc_id');
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
                'name' => 'woc_id',
                'equilavence' => 'work_order_contact.id'
            ],
            [
                'name' => 'firstname',
                'equilavence' => 'user.first_name'
            ],
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
