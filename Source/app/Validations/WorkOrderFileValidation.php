<?php
namespace App\Validations;

use App\Enums\ActionFileEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderFileService;
use Illuminate\Http\Response;

class WorkOrderFileValidation extends BaseValidation
{
    public $user;
    public $workOrder;
    public $workOrderFile;

    protected $workOrderValidation;
    protected $workOrderFileService;

    public function __construct(WorkOrderValidation $workOrderValidation, WorkOrderFileService $workOrderFileService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderFileService = $workOrderFileService;
    }

    public function store($request, $resource, $woID, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);

        $file_guid = StringHelper::Trim($request->get('guid'));
        $file = $request->file('file');


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
        // ============================ File ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $file, 'file');

        if($error_response)
            return $error_response;

        $error_response = $this->checkValueHasData($resource, $file, 'file');

        if($error_response)
            return $error_response;


        $action = $this->getActionByPhoto($file_guid);

        if($action == ActionFileEnum::CREATE)
        {
            // create new guid
            $file_guid = \FunctionHelper::CreateGUID(16);

        } else {

            //---------------------------
            // by guid
            //---------------------------
            $error_response = $this->checkFileGuidExists($resource, $woID, $file_guid, 'guid');

            if ($error_response)
                return $error_response;

        }

        $save['action'] = $action;
        $save['file'] = $file;
        $save['guid'] = $file_guid;


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

        $error_response = $this->checkFileGuidExists($resource, $workOrder->id, $file_guid, 'guid');

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;
        $this->workOrderFile = $this->workOrderFileService->getByWorkOrderAndGuid($workOrder->id, $file_guid);

        return null;
    }

    public function index($request, $resource, $woID, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $workOrder = WorkOrder::find($woID);

        $per_page = StringHelper::Trim($request->get('per_page'));
        $page = StringHelper::Trim($request->get('page'));

        $params = [
            'user' => $user,
            'work_order_id' => null,
            'per_page' => $per_page,
            'page' => $page,
        ];


        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        $params['work_order_id'] = $workOrder->id;

        // ------------------------------------------------------------------
        // ========================= Pagination =============================
        // ------------------------------------------------------------------
        $error_response = $this->checkPagination($resource, $page, $per_page);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Private Functions =========================
    // ------------------------------------------------------------------
    private function getActionByPhoto($guid)
    {
        $return = ActionFileEnum::CREATE;

        if($guid == null){
            $return = ActionFileEnum::CREATE;

        } elseif($guid != null) {
            $return = ActionFileEnum::EDIT;
        }

        return $return;
    }


    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    public function checkFileGuidExists($resource, $workOrderID, $guid, $field)
    {
        $data = $this->workOrderFileService->getByWorkOrderAndGuid($workOrderID, $guid);

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

    public function checkFileOrderExists($resource, $workOrderID, $order, $field, $guid = null)
    {
        $data =  $this->workOrderFileService->getFileOrderExists($workOrderID, $order, $guid);

        if($data != null){

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.already_exists', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::already_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);

        }

        return null;
    }


}
