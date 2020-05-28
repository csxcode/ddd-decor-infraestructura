<?php namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderCostCenter;
use App\Services\WorkOrderCostCenterService;
use Illuminate\Http\Response;

class WorkOrderCostCenterValidation extends BaseValidation
{
    public $workOrderCostCenter;
    protected $workOrderValidation;
    protected $costCenterValidation;
    protected $workOrderCostCenterService;

    public function __construct(WorkOrderValidation $workOrderValidation, CostCenterValidation $costCenterValidation, WorkOrderCostCenterService $workOrderCostCenterService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->costCenterValidation = $costCenterValidation;
        $this->workOrderCostCenterService = $workOrderCostCenterService;
    }

    public function store($request, $resource, $woID, &$save)
    {
        // --------------------------------------------
        // Check if json is valid
        // --------------------------------------------
        if(!$request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();


        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);

        $data = $request->get('data');
        $workOrder = WorkOrder::find($woID);

        $save = [];


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ======================= work_order_id ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ========================== data ==================================
        // ------------------------------------------------------------------
        $error_response = $this->checkArrayLength($resource, $data, 'data', trans('api/validation.array_no_one', ['attribute' => 'centros de costos']));

        if($error_response)
            return $error_response;


        // Check Only: required and valid values
        $totalPercentageEntered = 0;

        foreach ($data as $item)
        {
            $costCenterId =  array_key_exists('cost_center_id', $item) ? StringHelper::Trim($item['cost_center_id']) : null;
            $percent =  array_key_exists('percent', $item) ? StringHelper::Trim($item['percent']) : null;
            $field = 'cost_center_id (' . $costCenterId . ')';

            // ------------------------------------------------------------------
            // =================== cost_center_id ===============================
            // ------------------------------------------------------------------
            $error_response = $this->checkValueMustBeRequired($resource, $costCenterId, 'cost_center_id');

            if($error_response)
                return $error_response;

            $error_response = $this->costCenterValidation->checkCostCenter($resource, $costCenterId, $field);

            if($error_response)
                return $error_response;

            $error_response = $this->checkCostCenterExists($resource, $workOrder->id, $costCenterId, null, $field);

            if($error_response)
                return $error_response;


            // ------------------------------------------------------------------
            // ======================== percent ===============================
            // ------------------------------------------------------------------
            $error_response = $this->checkValueMustBeRequired($resource, $percent, 'percent');

            if($error_response)
                return $error_response;

            $error_response = $this->checkPercentValue($resource, $percent, 'percent');

            if($error_response)
                return $error_response;

            $totalPercentageEntered += $percent;
        }


        // Check Duplicates
        $error_response = $this->checkDuplicatesArrayByField($resource, $data, 'cost_center_id', 'cost_center_id', trans('api/validation.duplicated', ['attribute' => 'cost_center_id']));

        if($error_response)
            return $error_response;


        // Check others values (final)
        foreach ($data as $item)
        {
            // ------------------------------------------------------------------
            // ======================== percent ===============================
            // ------------------------------------------------------------------
            $error_response = $this->checkPercentTotalByWorkOrder($resource, $totalPercentageEntered, $workOrder->id, 'percent');

            if($error_response)
                return $error_response;

            // ------------------------------------------------------------------
            # Add data to then create in mass
            // ------------------------------------------------------------------
            $costCenter = [
                'work_order_id' => $workOrder->id,
                'cost_center_id' => $item['cost_center_id'],
                'percent' => $item['percent'],
            ];

            array_push($save, $costCenter);
        }


        // ------------ Return ----------------
        //--------------------------------------
        return null;
    }

    public function destroy($request, $resource, $woID, $code)
    {
        // --------------------------------------------
        // Get Data
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderCostCenter = $this->workOrderCostCenterService->getByWorkOrderAndCode($woID, $code);

        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ================== code ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueHasData($resource, $workOrderCostCenter, 'code '. $code);

        if($error_response)
            return $error_response;

        // ------------ Return ----------------
        //--------------------------------------
        $this->workOrderCostCenter = $workOrderCostCenter;

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
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------
    public function checkPercentValue($resource, $value, $field)
    {
        // check if is an integer
        if (!is_numeric($value)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        // check between values
        if (!($value >=0 && $value <=100)) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.between.numeric', ['attribute' => $field, 'min' => 0, 'max' => 100]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkPercentTotalByWorkOrder($resource, $value, $workOrderId, $field)
    {
        // Tener en cuenta que la suma de todos los centros de costos no debe superar a 100%.
        $cc_percent_sum = WorkOrderCostCenter::where('work_order_id', $workOrderId)->sum('percent');
        $cc_percent_sum += $value;

        if ($cc_percent_sum > 100) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.work_order_cost_center.percente_total_has_exceeded'),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkCostCenterExists($resource, $workOrderId, $costCenterId, $id, $field)
    {
        $data =  $this->workOrderCostCenterService->getCostCenterExists($workOrderId, $costCenterId, $id);

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
