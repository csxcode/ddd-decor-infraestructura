<?php
namespace App\Validations;

use App\Enums\AppObjectNameEnum;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderHistoryService;

class WorkOrderHistoryVideoValidation extends BaseValidation
{
    public $user;
    public $workOrder;
    public $workOrderHistory;

    private $workOrderValidation;
    private $workOrderHistoryValidation;
    private $workOrderHistoryService;

    public function __construct(WorkOrderValidation $workOrderValidation, WorkOrderHistoryValidation $workOrderHistoryValidation,
        WorkOrderHistoryService $workOrderHistoryService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderHistoryValidation = $workOrderHistoryValidation;
        $this->workOrderHistoryService = $workOrderHistoryService;
    }

    public function store($request, $resource, $woID, $wohID, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderHistory = $this->workOrderHistoryService->findByWorkOrder($wohID, $woID);

        $file = $request->file('video');

        $save = [
            'file' => null,
        ];

        // ------------------------------------------------------------------
        // ======================= workOrder ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ======================= workOrderHistory =========================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderHistoryValidation->checkValueHasData(AppObjectNameEnum::WORK_ORDER_HISTORY, $workOrderHistory, 'work_order_history_id');

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =========================== Video ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $file, 'video');

        if($error_response)
            return $error_response;

        $error_response = $this->checkValueHasData($resource, $file, 'video');

        if($error_response)
            return $error_response;

        $error_response = $this->checkVideoExtensionIsAllowed($resource, $file, 'video');

        if($error_response)
            return $error_response;

        $save['file'] = $file;

        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;
        $this->workOrderHistory = $workOrderHistory;

        return null;
    }


}
