<?php
namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\ActionFileEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderVideoService;
use Illuminate\Http\Response;

class WorkOrderVideoValidation extends BaseValidation
{
    public $user;
    public $workOrder;
    private $workOrderValidation;
    private $workOrderVideoService;

    public function __construct(WorkOrderValidation $workOrderValidation, WorkOrderVideoService $workOrderVideoService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderVideoService = $workOrderVideoService;
    }

    public function store($request, $resource, $woID, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);

        $file = $request->file('video');

        $save = [
            'file' => null,
        ];

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ======================= workOrder ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

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

        return null;
    }

    public function destroy($request, $resource, $woID)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);

        $file_guid = StringHelper::Trim($request->get('guid'));


        // ------------------------------------------------------------------
        // ======================= workOrder ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionFileEnum::CREATE);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ======================= file_guid ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $file_guid, 'guid');

        if($error_response)
            return $error_response;

        $error_response = $this->checkVideoGuidExists($resource, $workOrder->id, $file_guid, 'guid');

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;

        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    public function checkVideoGuidExists($resource, $workOrderID, $guid, $field)
    {
        $data = $this->workOrderVideoService->getByWorkOrderAndGuid($workOrderID, $guid);

        if($data == null){

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => $field.': ' . $guid]),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_NOT_FOUND);

        }

        return null;
    }

}
