<?php namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderStatus;
use App\Services\WorkOrderSearchViewService;
use Illuminate\Http\Response;

class WorkOrderValidation extends BaseValidation
{
    public $workOrder;
    protected $workOrderSearchViewService;
    protected $branchLocationValidation;
    protected $majorAccountValidation;

    public function __construct(WorkOrderSearchViewService $workOrderSearchViewService,
        BranchLocationValidation $branchLocationValidation, MajorAccountValidation $majorAccountValidation)
    {
        $this->workOrderSearchViewService = $workOrderSearchViewService;
        $this->branchLocationValidation = $branchLocationValidation;
        $this->majorAccountValidation = $majorAccountValidation;
    }

    public function store($request, $resource, &$save)
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

        $required_days = StringHelper::Trim($request->get('required_days'));
        $work_specs = StringHelper::Trim($request->get('work_specs'));
        $branch_location = StringHelper::Trim($request->get('branch_location'));
        $major_account = StringHelper::Trim($request->get('major_account'));
        $sap_description = StringHelper::Trim($request->get('sap_description'));

        $save = [
            'required_days' => null,
            'work_specs' => null,
            'branch_location_id' => null,
            'major_account_id' => null,
            'sap_description' => null,
        ];


        // ------------------------------------------------------------------
        // ======================= required_days ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkRequiredDays($resource, $required_days);

        if($error_response)
            return $error_response;

         // Set data to save
         $save['required_days'] = $required_days;


        // ------------------------------------------------------------------
        // ======================= work_specs ===============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkSpefications($resource, $work_specs);

        if($error_response)
            return $error_response;

         // Set data to save
         $save['work_specs'] = $work_specs;


        // ------------------------------------------------------------------
        // ================== branch_location ===============================
        // ------------------------------------------------------------------
        $error_response = BranchLocationValidation::checkBranchLocation($resource, $branch_location, $user, 'branch_location');

        if($error_response)
            return $error_response;

        // Set data to save
        $save['branch_location_id'] = $branch_location;


        // ------------------------------------------------------------------
        // ==================== major_account ===============================
        // ------------------------------------------------------------------
        $error_response = $this->majorAccountValidation->checkMajorAccount($resource, $major_account, 'major_account');

        if($error_response)
            return $error_response;

        // Set data to save
        $save['major_account_id'] = $major_account;


        // ------------------------------------------------------------------
        // ================== sap_description ===============================
        // ------------------------------------------------------------------
        $error_response = $this->checkSapDescription($resource, $sap_description);

        if($error_response)
            return $error_response;

        $error_response = $this->checkLen($resource, $sap_description, 'sap_description', 40);

        if($error_response)
            return $error_response;

        // Set data to save
        $save['sap_description'] = $sap_description;


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;

        return null;
    }

    public function update($request, $resource, $id, &$changes)
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
        $work_order = WorkOrder::find($id);

        $required_days = StringHelper::Trim($request->get('required_days'));
        $work_specs = StringHelper::Trim($request->get('work_specs'));
        $branch_location = StringHelper::Trim($request->get('branch_location'));
        $major_account = StringHelper::Trim($request->get('major_account'));
        $sap_description = StringHelper::Trim($request->get('sap_description'));

        $work_order_clone = null;

        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkOrder($resource, $work_order);

        if($error_response) {
            return $error_response;
        } else {
            $work_order_clone = clone $work_order;
        }

        $error_response = $this->checkIfWorkOrderCanBeUpdated($resource, $work_order);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ======================= required_days ============================
        // ------------------------------------------------------------------
        if($request->exists('required_days')) {

            $error_response = $this->checkRequiredDays($resource, $required_days);

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('required_days', $work_order_clone->required_days, $required_days, $work_order, $changes);
        }


        // ------------------------------------------------------------------
        // ======================= work_specs ===============================
        // ------------------------------------------------------------------
        if($request->exists('work_specs')) {

            $error_response = $this->checkWorkSpefications($resource, $work_specs);

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('work_specs', $work_order_clone->work_specs, $work_specs, $work_order, $changes);
        }


        // ------------------------------------------------------------------
        // ================== branch_location ============================
        // ------------------------------------------------------------------
        if($request->exists('branch_location')) {

            $error_response = BranchLocationValidation::checkBranchLocation($resource, $branch_location, $user, 'branch_location');

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('branch_location_id', $work_order_clone->branch_location_id, $branch_location, $work_order, $changes);
        }


        // ------------------------------------------------------------------
        // ==================== major_account ===============================
        // ------------------------------------------------------------------
        if($request->exists('major_account')) {

            $error_response = $this->majorAccountValidation->checkMajorAccount($resource, $major_account, 'major_account');

            if($error_response)
                return $error_response;

                $this->checkThereIsChangesAndSetToModel('major_account_id', $work_order_clone->major_account_id, $major_account, $work_order, $changes);
        }


        // ------------------------------------------------------------------
        // ================== sap_description ===============================
        // ------------------------------------------------------------------
        if($request->exists('sap_description')) {

            $error_response = $this->checkSapDescription($resource, $sap_description);

            if($error_response)
                return $error_response;

            $error_response = $this->checkLen($resource, $sap_description, 'sap_description', 40);

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('sap_description', $work_order_clone->sap_description, $sap_description, $work_order, $changes);
        }


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ================ Check fields excluded ===========================
        // ------------------------------------------------------------------
        $excludedFields = [
            'wo_number', 'work_order_status_id', 'video_guid', 'video_name',
            'start_date', 'end_date', 'ticket_id', 'maintenance_id'
        ];

        $error_response = $this->checkExcludedFields($resource, $request->all(), $excludedFields);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $work_order;

        return null;
    }

    public function show($request, $resource, $id)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $work_order = $this->workOrderSearchViewService->findByRole($id, $user);


        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkOrder($resource, $work_order);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $work_order;

        return null;
    }

    public function index($request, $resource, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());

        $status_id = StringHelper::Trim($request->get('status_id'));
        $branch_id = StringHelper::Trim($request->get('branch_id'));
        $ticket_number = StringHelper::Trim($request->get('ticket_number'));
        $wo_number = StringHelper::Trim($request->get('wo_number'));
        $per_page = StringHelper::Trim($request->get('per_page'));
        $page = StringHelper::Trim($request->get('page'));

        $params = [
            'user' => $user,
            'status_id' => $status_id,
            'branch_id' => $branch_id,
            'ticket_number' => $ticket_number,
            'wo_number' => $wo_number,
            'per_page' => $per_page,
            'page' => $page,
        ];

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
    public function checkRoleIsAllowed($resource, $role, $action)
    {
        if ($action == ActionEnum::CREATE || $action == ActionEnum::EDIT) {

            if ($role != UserRoleEnum::GESTOR_INFRAESTRUCTURA)
            {
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

        }

        return null;
    }

    public function checkWorkOrder($resource, $work_order)
    {
        if (!$work_order) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'work_order']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function checkRequiredDays($resource, $required_days)
    {
        if (StringHelper::IsNullOrEmptyString($required_days)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'required_days']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'required_days'
                ]
            ], Response::HTTP_BAD_REQUEST);

        } else {

            if (!ctype_digit($required_days)) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_valid', ['attribute' => 'required_days']),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'required_days',
                    ]
                ], Response::HTTP_BAD_REQUEST);

            }

        }
    }

    public function checkWorkSpefications($resource, $work_specs)
    {
        if (StringHelper::IsNullOrEmptyString($work_specs)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'work_specs']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'work_specs'
                ]
            ], Response::HTTP_BAD_REQUEST);

        }
    }

    public function checkSapDescription($resource, $sap_description)
    {
        if (StringHelper::IsNullOrEmptyString($sap_description)) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'sap_description']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'sap_description'
                ]
            ], Response::HTTP_BAD_REQUEST);

        }
    }

    public function checkIfWorkOrderCanBeUpdated($resource, $workOrder)
    {
         // No debe permitir actualizar las WO con estado cerrado (7) y anulado (8).
         if ($workOrder->work_order_status_id == WorkOrderStatus::STATUS_CERRADO || $workOrder->work_order_status_id == WorkOrderStatus::STATUS_ANULADO) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.wo.cannot_edit_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }
    }
}
