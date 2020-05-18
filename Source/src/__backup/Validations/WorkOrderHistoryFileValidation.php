<?php
namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderHistoryService;
use Illuminate\Http\Response;

class WorkOrderHistoryFileValidation extends BaseValidation
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

        $file = $request->file('file');

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
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ============================ File ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $file, 'file');

        if($error_response)
            return $error_response;

        $error_response = $this->checkValueHasData($resource, $file, 'file');

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


    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    public function checkRoleIsAllowed($resource, $role, $action)
    {
        if ($action == ActionEnum::CREATE)
        {
            $pass = false;

            if ($role == UserRoleEnum::RESPONSABLE_SEDE || $role == UserRoleEnum::GESTOR_INFRAESTRUCTURA)
                $pass = true;

            if(!$pass)
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.forbidden_role_user'),
                    'error_code' => ErrorCodesEnum::forbidden,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'user'
                    ]
                ], Response::HTTP_FORBIDDEN);

        }

        return null;
    }

}
