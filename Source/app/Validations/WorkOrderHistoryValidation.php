<?php

namespace App\Validations;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderStatus;
use App\Services\WorkOrderHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Response;

class WorkOrderHistoryValidation extends BaseValidation
{
    public $user;
    public $workOrderHistory;

    private $workOrderValidation;
    private $workOrderStatusValidation;
    private $workOrderHistoryService;

    public function __construct(WorkOrderValidation $workOrderValidation, WorkOrderStatusValidation $workOrderStatusValidation, WorkOrderHistoryService $workOrderHistoryService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderStatusValidation = $workOrderStatusValidation;
        $this->workOrderHistoryService = $workOrderHistoryService;
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

        $work_order_status_id = StringHelper::Trim($request->get('work_order_status_id'));
        $start_date = StringHelper::Trim($request->get('start_date'));
        $end_date = StringHelper::Trim($request->get('end_date'));
        $work_report = StringHelper::Trim($request->get('work_report'));
        $workOrder = WorkOrder::find($woID);

        $save = [
            'work_order_id' => null,
            'work_order_status_id' => null,
            'start_date' => null,
            'end_date' => null,
            'work_report' => null,
        ];

        // ------------------------------------------------------------------
        // ======================= work_order_id ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        $save['work_order_id'] = $workOrder->id;

        // ------------------------------------------------------------------
        // =================== work_order_status_id =========================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkValueMustBeRequired($resource, $work_order_status_id, 'work_order_status_id');

        if($error_response)
            return $error_response;

        $error_response = $this->checkStatusIdIsAllowedToBeUseByUserRole($resource, $workOrder, $user, $work_order_status_id, $role, 'work_order_status_id');

        if($error_response)
            return $error_response;

        $save['work_order_status_id'] = $work_order_status_id;

        // ------------------------------------------------------------------
        // ====================== work_report ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkValueMustBeRequired($resource, $work_report, 'work_report');

        if($error_response)
            return $error_response;

        $save['work_report'] = $work_report;


        // ------------------------------------------------------------------
        // ============= Check some fields by role ==========================
        // ------------------------------------------------------------------
        if($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA)
        {
            // ------------------------------------------------------------------
            // ======================= start_date ===============================
            // ------------------------------------------------------------------
            if($request->exists('start_date'))
            {
                $error_response = $this->checkDateTimestampIsValid($resource, $start_date, 'start_date');

                if($error_response)
                    return $error_response;

                $save['start_date'] = Carbon::createFromTimestamp($start_date);
            }

            // ------------------------------------------------------------------
            // ========================= end_date ===============================
            // ------------------------------------------------------------------
            if($request->exists('end_date'))
            {
                $error_response = $this->checkDateTimestampIsValid($resource, $end_date, 'end_date');

                if($error_response)
                    return $error_response;

                $save['end_date'] = Carbon::createFromTimestamp($end_date);

            }

        }


        // ------------ Return ----------------
        //-------------------------------------
        $this->user = $user;
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

    public function show($request, $resource, $woID, $wohID)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderHistory = $this->workOrderHistoryService->findByWorkOrderAndRole($wohID, $woID, $user);

        // ------------------------------------------------------------------
        // ==================== work_order ==================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ==================== work_order_history ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueHasData($resource, $workOrderHistory, 'work_order_history_id');

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrderHistory = $workOrderHistory;

        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    function checkStatusIdIsAllowedToBeUseByUserRole($resource, $workOrder, $user, $workOrderStatusId, $role, $field)
    {
        // check if status is valid
        $error_response = $this->workOrderStatusValidation->checkStatus($resource, $workOrderStatusId, $field);

        if ($error_response)
            return $error_response;

        // check status types
        $statusAllowed = false;

        if ($workOrderStatusId == WorkOrderStatus::STATUS_CONFIRMADO) {

            if ($role == UserRoleEnum::RESPONSABLE_SEDE || $role == UserRoleEnum::GESTOR_INFRAESTRUCTURA)
                $statusAllowed = true;

        } elseif (
            $workOrderStatusId == WorkOrderStatus::STATUS_CERRADO ||
            $workOrderStatusId == WorkOrderStatus::STATUS_ANULADO ||
            $workOrderStatusId == WorkOrderStatus::STATUS_REAPERTURADO ||
            $workOrderStatusId == WorkOrderStatus::STATUS_COTIZANDO
        ) {

            if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA)
                $statusAllowed = true;

        } else {

            if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA ||
                $role == UserRoleEnum::RESPONSABLE_SEDE ||
                $role == UserRoleEnum::PROVEEDOR){

                $statusAllowed = true;

                /**
                 * El Gestor de Infraestructura (4) puede crear history en cualquier work order
                 * El responsable de sede (5) pueden crear history solo en las work order de la sucursal que tenga asignado
                 * El proveedor puede crear history solo en las work order que tiene asignado, revisar la regla 3.6.B
                 * The scope called "filterByRoleForWOH" already make these rules
                 */
                $exists = WorkOrderSearch::where('id', $workOrder->id)
                        ->filterByRoleForWOH($user)->count() > 0;

                if(!$exists){
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
        }

        // return error if status does not allowed
        if (!$statusAllowed) {
            $workOrderStatus = WorkOrderStatus::find($workOrderStatusId);

            $object = 'un work_order_history';
            $field = 'estado: ' . $workOrderStatus->name;

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user_by_object_field', ['object' => $object, 'attribute' => $field]),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_FORBIDDEN);
        }


        // Check some others validations
        if ($statusAllowed) {

            $pass = true;

            // ---------------------------------------------------------------------------------------
            // Check when work_order is closed only can be updated with status "reaperturado"
            // ---------------------------------------------------------------------------------------
            # Si el estado actual de la Work Order es “Cerrado” (7), solo se debe permitir agregar History con estado “Reapertura” (9)
            # Es decir, la única acción posible cuando la Work Order está cerrada, es reapertura.
            if ($workOrder->work_order_status_id == WorkOrderStatus::STATUS_CERRADO) {
                if($workOrderStatusId != WorkOrderStatus::STATUS_REAPERTURADO) {
                    $pass = false;
                }
            }

            if(!$pass){
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.work_order_history.store_wo_is_closed_status_reaperture'),
                    'error_code' => ErrorCodesEnum::not_valid,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => $field
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

        }

        return null;
    }

}
