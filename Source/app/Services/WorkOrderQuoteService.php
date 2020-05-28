<?php
namespace App\Services;

use App\Enums\AccessTypeEnum;
use App\Enums\QuoteNotificationEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\ArrayHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Models\WorkOrder\WorkOrderQuoteStatus;
use App\UseCases\WorkOrderQuote\GenerateWorkOrderHistory;
use App\UseCases\WorkOrderQuote\NotifyAssignWorkOrderByEmail;
use App\UseCases\WorkOrderQuote\NotifyEmailWhenStatusIsQuoted;
use App\UseCases\WorkOrderQuote\NotifyQuoteInvitationByEmail;

class WorkOrderQuoteService
{
    protected $workOrderContactService;

    public function __construct(WorkOrderContactService $workOrderContactService)
    {
        $this->workOrderContactService = $workOrderContactService;
    }

    public function create($data, $user)
    {
        $data['quote_status_id'] = WorkOrderQuoteStatus::STATUS_PENDIENTE;
        $data['created_at'] = now();
        $data['created_by_user'] = User::GetCreatedByUser($user);
        $data['updated_at'] = null;

        return WorkOrderQuote::create($data);
    }

    public function massCreate($data, $user)
    {
        $createdAt = now();
        $createdByUser = User::GetCreatedByUser($user);
        $save = [];

        foreach ($data as $item)
        {
            $item['quote_status_id'] = WorkOrderQuoteStatus::STATUS_PENDIENTE;
            $item['created_at'] = $createdAt;
            $item['created_by_user'] = $createdByUser;
            $item['updated_at'] = null;

            array_push($save, $item);
        }

        return WorkOrderQuote::insert($save);
    }

    public function update(WorkOrderQuote $workOrderQuote, $user) : WorkOrderQuote
    {
        // clone object with the old data to validation later
        $workOrderQuoteOld = WorkOrderQuote::find($workOrderQuote->id);

        // Update Process
        $data = $this->updateWOQ($workOrderQuote, $user);

        // Notification Quote Invitation
        $notifyQuoteInvitation = new NotifyQuoteInvitationByEmail();
        $wasNotified = $notifyQuoteInvitation->run($workOrderQuoteOld, $data);

        if($wasNotified){
            $data->notification = QuoteNotificationEnum::Enviada;
            $data = $this->updateWOQ($workOrderQuote, $user);
        }

        (new GenerateWorkOrderHistory())->run($workOrderQuoteOld, $data, $user);
        (new NotifyAssignWorkOrderByEmail())->run($workOrderQuoteOld, $data);

        if($this->checkIfShouldSendEmailQuoted($user, $workOrderQuoteOld, $workOrderQuote))
            (new NotifyEmailWhenStatusIsQuoted())->execute($data);

        return $data;
    }

    public function delete(WorkOrderQuote $workOrderQuote)
    {
        $workOrderQuote->delete();
    }

    public function getByWorkOrderAndVendor($woID, $vendorID, $exclude_woq_id)
    {
        $query = WorkOrderQuote::where('work_order_id', $woID)->where('vendor_id', $vendorID);

        if($exclude_woq_id)
            $query->where('id', '<>', $exclude_woq_id);

        return $query->first();
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
                work_order_quote.id,
                work_order_quote.vendor_id,
                work_order_quote.quote_status_id
            ';

        } else if($accessType == AccessTypeEnum::Web) {
        }

        $columns = ltrim(rtrim($columns));
        $query = WorkOrderQuote::select(\DB::raw($columns))->with(['vendor', 'status']);


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

    public function findByWorkOrderAndRole(int $id, $workOrderId, User $user){

        $query = WorkOrderQuote::where('id', $id);
        $query->where('work_order_id', $workOrderId);
        $query->filterByRole($user);

        return $query->first();
    }

    public function findByWorkOrder(int $id, $workOrderId)
    {
        $query = WorkOrderQuote::where('id', $id)->where('work_order_id', $workOrderId);
        return $query->first();
    }

    // =====================================================================
    // ========================= Private ===================================
    // =====================================================================
    private function columnsEquivalenceToSearch(){
        return array(
            [
                'name' => 'woq_id',
                'equilavence' => 'work_order_quote.id'
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

    private function updateWOQ(WorkOrderQuote $data, $user)
    {
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }

    private function checkIfShouldSendEmailQuoted($user, WorkOrderQuote $oldWorkOrderQuote, WorkOrderQuote $newWorkOrderQuote)
    {
        $hasChangedFromPendienteToCotizado =
            $oldWorkOrderQuote->quote_status_id == WorkOrderQuoteStatus::STATUS_PENDIENTE &&
            $newWorkOrderQuote->quote_status_id == WorkOrderQuoteStatus::STATUS_COTIZADO;

        $userIsVendorType = $user->role->name == UserRoleEnum::PROVEEDOR;

        if($userIsVendorType && $hasChangedFromPendienteToCotizado){
            return true;
        }

        return false;
    }
}
