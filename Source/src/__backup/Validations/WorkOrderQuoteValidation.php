<?php


namespace App\Validations;


use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\QuoteNotificationEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Models\WorkOrder\WorkOrderStatus;
use App\Services\WorkOrderQuoteService;
use Illuminate\Http\Response;

class WorkOrderQuoteValidation extends BaseValidation
{
    public $user;
    public $workOrderQuote;

    protected $vendorValidation;
    protected $workOrderValidation;
    protected $workOrderQuoteStatusValidation;

    protected $workOrderQuoteService;


    public function __construct(VendorValidation $vendorValidation, WorkOrderValidation $workOrderValidation, WorkOrderQuoteService $workOrderQuoteService,
        WorkOrderQuoteStatusValidation $workOrderQuoteStatusValidation)
    {
        $this->vendorValidation = $vendorValidation;
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderQuoteService = $workOrderQuoteService;
        $this->workOrderQuoteStatusValidation = $workOrderQuoteStatusValidation;
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

        $workOrder = WorkOrder::find($woID);
        $data = $request->get('data');

        $save = [];

        // ------------------------------------------------------------------
        // ======================= workOrder ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =============== by status of work_order ==========================
        // ------------------------------------------------------------------
        $error_response = $this->checkStatusWorkOrderIsQuoting($workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ========================== data ==================================
        // ------------------------------------------------------------------
        $error_response = $this->checkArrayLength($resource, $data, 'data', trans('api/validation.array_no_one', ['attribute' => 'presupuestos']));

        if($error_response)
            return $error_response;

        foreach ($data as $item)
        {
            $vendor_id =  array_key_exists('vendor_id', $item) ? StringHelper::Trim($item['vendor_id']) : null;
            $amount =  array_key_exists('amount', $item) ? StringHelper::Trim($item['amount']) : null;
            $currency =  array_key_exists('currency', $item) ? StringHelper::Trim($item['currency']) : null;
            $payment_type =  array_key_exists('payment_type', $item) ? StringHelper::Trim($item['payment_type']) : null;
            $work_terms =  array_key_exists('work_terms', $item) ? StringHelper::Trim($item['work_terms']) : null;
            $notes =  array_key_exists('notes', $item) ? StringHelper::Trim($item['notes']) : null;
            $time_days =  array_key_exists('time_days', $item) ? StringHelper::Trim($item['time_days']) : null;
            $time_hours =  array_key_exists('time_hours', $item) ? StringHelper::Trim($item['time_hours']) : null;

            // ------------------------------------------------------------------
            // ======================= vendor_id ================================
            // ------------------------------------------------------------------
            $field = 'vendor_id (' . $vendor_id . ')';
            $error_response = $this->checkValueMustBeRequired($resource, $vendor_id, $field);

            if($error_response)
                return $error_response;

            $error_response = $this->vendorValidation->checkVendor($resource, $vendor_id, $field);

            if($error_response)
                return $error_response;

            $error_response = $this->checkVendorDoesNotDuplicate($resource, $workOrder->id, $vendor_id, null, $field);

            if($error_response)
                return $error_response;

            // ------------------------------------------------------------------
            // ======================= amount ===================================
            // ------------------------------------------------------------------
            $field = 'amount (' . $amount . ')';
            if(array_key_exists('amount', $item)) {
                $error_response = $this->checkDecimal($resource, $amount, $field, 2);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ======================= currency =================================
            // ------------------------------------------------------------------
            $field = 'currency (' . $currency . ')';
            if(array_key_exists('currency', $item)) {
                $error_response = $this->checkCurrency($resource, $currency, $field);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ======================= payment_type =============================
            // ------------------------------------------------------------------
            $field = 'payment_type (' . $payment_type . ')';
            if(array_key_exists('payment_type', $item)) {
                $error_response = $this->checkPaymentType($resource, $payment_type, $field);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ======================= work_terms ===============================
            // ------------------------------------------------------------------
            if(array_key_exists('work_terms', $item)) {
                $error_response = $this->checkLen($resource, $work_terms, 'work_terms', 250);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ============================ notes ===============================
            // ------------------------------------------------------------------
            if(array_key_exists('notes', $item)) {
                $error_response = $this->checkLen($resource, $work_terms, 'notes', 250);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ============================ time_days ===========================
            // ------------------------------------------------------------------
            $field = 'time_days (' . $time_days . ')';
            if(array_key_exists('time_days', $item)) {
                $error_response = $this->checkIsInteger($resource, $time_days, $field);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ============================ time_hours ==========================
            // ------------------------------------------------------------------
            $field = 'time_hours (' . $time_hours . ')';
            if($request->exists('time_hours')) {
                $error_response = $this->checkIsInteger($resource, $time_hours, $field);

                if($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            # Add data to then create in mass
            // ------------------------------------------------------------------
            $quote = [
                'work_order_id' => $workOrder->id,
                'vendor_id' => $vendor_id,
                'amount' => $amount,
                'currency' => $currency,
                'payment_type' => $payment_type,
                'work_terms' => $work_terms,
                'notes' => $notes,
                'time_days' => $time_days,
                'time_hours' => $time_hours,
            ];

            array_push($save, $quote);
        }


        // ------------------------------------------------------------------
        // Check Duplicates by vendor
        // ------------------------------------------------------------------
        $error_response = $this->checkDuplicatesArrayByField($resource, $data, 'vendor_id', 'vendor_id', trans('api/validation.duplicated', ['attribute' => 'vendor_id']));

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;

        return null;
    }

    public function update($request, $resource, $woID, $woqID, &$changes)
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
        $workOrder = WorkOrder::find($woID);
        $workOrderQuote = WorkOrderQuote::find($woqID);

        $status_id = StringHelper::Trim($request->get('status_id'));
        $vendor_id = StringHelper::Trim($request->get('vendor_id'));
        $amount = StringHelper::Trim($request->get('amount'));
        $currency = StringHelper::Trim($request->get('currency'));
        $time_days = StringHelper::Trim($request->get('time_days'));
        $time_hours = StringHelper::Trim($request->get('time_hours'));
        $payment_type = StringHelper::Trim($request->get('payment_type'));
        $work_terms = StringHelper::Trim($request->get('work_terms'));
        $notes = StringHelper::Trim($request->get('notes'));
        $notification = StringHelper::Trim($request->get('notification'));

        $workOrderQuoteClone = null;

        // ------------------------------------------------------------------
        // ==================== work_order ==================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ==================== work_order_quote ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkOrderQuote($resource, $workOrderQuote);

        if($error_response) {
            return $error_response;
        } else {
            $workOrderQuoteClone = clone $workOrderQuote;
        }

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =============== by status of work_order ==========================
        // ------------------------------------------------------------------
        $error_response = $this->checkStatusWorkOrderIsQuoting($workOrder);

        if($error_response)
            return $error_response;


        // ==================================================================
        // ==================================================================
        // =================== Rules by Role ================================
        // ==================================================================
        // ==================================================================

        if($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA)
        {
            // ------------------------------------------------------------------
            // ================= status_id ================================
            // ------------------------------------------------------------------
            if($request->exists('status_id')) {
                $error_response = $this->workOrderQuoteStatusValidation->checkStatus($resource, $status_id, 'status_id');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('quote_status_id', $workOrderQuoteClone->quote_status_id, $status_id, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= vendor_id ================================
            // ------------------------------------------------------------------
            if($request->exists('vendor_id')) {
                $error_response = $this->vendorValidation->checkVendor($resource, $vendor_id, 'vendor_id');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('vendor_id', $workOrderQuoteClone->vendor_id, $vendor_id, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============ check vendor does not duplicate =====================
            // ------------------------------------------------------------------
            if($request->exists('vendor_id')) {
                $error_response = $this->checkVendorDoesNotDuplicate($resource, $workOrder->id, $vendor_id, $workOrderQuote->id, 'vendor_id');

                if ($error_response)
                    return $error_response;
            }

            // ------------------------------------------------------------------
            // ======================= amount ===================================
            // ------------------------------------------------------------------
            if($request->exists('amount')) {
                $error_response = $this->checkDecimal($resource, $amount, 'amount', 2);

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('amount', $workOrderQuoteClone->amount, $amount, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= currency =================================
            // ------------------------------------------------------------------
            if($request->exists('currency')) {
                $error_response = $this->checkCurrency($resource, $currency, 'currency');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('currency', $workOrderQuoteClone->currency, $currency, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============================ time_days ===========================
            // ------------------------------------------------------------------
            if($request->exists('time_days')) {
                $error_response = $this->checkIsInteger($resource, $time_days, 'time_days');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('time_days', $workOrderQuoteClone->time_days, $time_days, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============================ time_hours ==========================
            // ------------------------------------------------------------------
            if($request->exists('time_hours')) {
                $error_response = $this->checkIsInteger($resource, $time_hours, 'time_hours');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('time_hours', $workOrderQuoteClone->time_hours, $time_hours, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= payment_type =============================
            // ------------------------------------------------------------------
            if($request->exists('payment_type')) {
                $error_response = $this->checkPaymentType($resource, $payment_type, 'payment_type');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('payment_type', $workOrderQuoteClone->payment_type, $payment_type, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= work_terms ===============================
            // ------------------------------------------------------------------
            if($request->exists('work_terms')) {
                $error_response = $this->checkLen($resource, $work_terms, 'work_terms', 250);

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('work_terms', $workOrderQuoteClone->work_terms, $work_terms, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============================ notes ===============================
            // ------------------------------------------------------------------
            if($request->exists('notes')) {
                $error_response = $this->checkLen($resource, $work_terms, 'notes', 250);

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('notes', $workOrderQuoteClone->notes, $notes, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ===================== notification ===============================
            // ------------------------------------------------------------------
            if($request->exists('notification')) {
                $error_response = $this->checkNotification($resource, $notification, 'notification');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('notification', $workOrderQuoteClone->notification, $notification, $workOrderQuote, $changes);
            }


        } elseif ($role == UserRoleEnum::PROVEEDOR)
        {

            // ------------------------------------------------------------------------------------------
            // El usuario "Proveedor" puede actualizar solo si la cotización le pertenece al proveedor
            // ------------------------------------------------------------------------------------------
            $error_response = $this->checkWorkOrderQuoteBelongsToUser($resource, $workOrderQuote, $user);

            if($error_response)
                return $error_response;

            // ------------------------------------------------------------------
            // ======================= amount ===================================
            // ------------------------------------------------------------------
            if($request->exists('amount')) {
                $error_response = $this->checkDecimal($resource, $amount, 'amount', 2);

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('amount', $workOrderQuoteClone->amount, $amount, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= currency =================================
            // ------------------------------------------------------------------
            if($request->exists('currency')) {
                $error_response = $this->checkCurrency($resource, $currency, 'currency');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('currency', $workOrderQuoteClone->currency, $currency, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============================ time_days ===========================
            // ------------------------------------------------------------------
            if($request->exists('time_days')) {
                $error_response = $this->checkIsInteger($resource, $time_days, 'time_days');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('time_days', $workOrderQuoteClone->time_days, $time_days, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============================ time_hours ==========================
            // ------------------------------------------------------------------
            if($request->exists('time_hours')) {
                $error_response = $this->checkIsInteger($resource, $time_hours, 'time_hours');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('time_hours', $workOrderQuoteClone->time_hours, $time_hours, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= payment_type =============================
            // ------------------------------------------------------------------
            if($request->exists('payment_type')) {
                $error_response = $this->checkPaymentType($resource, $payment_type, 'payment_type');

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('payment_type', $workOrderQuoteClone->payment_type, $payment_type, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ======================= work_terms ===============================
            // ------------------------------------------------------------------
            if($request->exists('work_terms')) {
                $error_response = $this->checkLen($resource, $work_terms, 'work_terms', 250);

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('work_terms', $workOrderQuoteClone->work_terms, $work_terms, $workOrderQuote, $changes);
            }

            // ------------------------------------------------------------------
            // ============================ notes ===============================
            // ------------------------------------------------------------------
            if($request->exists('notes')) {
                $error_response = $this->checkLen($resource, $work_terms, 'notes', 250);

                if($error_response)
                    return $error_response;

                $this->checkThereIsChangesAndSetToModel('notes', $workOrderQuoteClone->notes, $notes, $workOrderQuote, $changes);
            }


            // ------------------------------------------------------------------
            // ================ Check fields excluded ===========================
            // ------------------------------------------------------------------
            $excludedFields = ['status_id', 'vendor_id', 'notification'];

            $error_response = $this->checkExcludedFields($resource, $request->all(), $excludedFields);

            if($error_response)
                return $error_response;

        }

        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }

    public function destroy($request, $resource, $woID, $woqID)
    {
        // --------------------------------------------
        // Get Data
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderQuote = WorkOrderQuote::where('work_order_id', $woID)->where('id', $woqID)->first();


        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ================== work_order_quote ==============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkOrderQuote($resource, $workOrderQuote);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =============== by status of work_order ==========================
        // ------------------------------------------------------------------
        /**
         * Permitir eliminar la cotización (work_order_quote) solo si el estado de la Work Order es Cotizando
         */

        $error_response = $this->checkStatusWorkOrderIsQuoting($workOrder);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }

    public function index($request, $resource, $woID, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
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
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::LIST);

        if($error_response)
            return $error_response;

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

    public function show($request, $resource, $woID, $woqID)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderQuote = $this->workOrderQuoteService->findByWorkOrderAndRole($woqID, $woID, $user);

        // ------------------------------------------------------------------
        // ==================== work_order ==================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ==================== work_order_quote ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkOrderQuote($resource, $workOrderQuote);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::LIST);

        if($error_response)
            return $error_response;

        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------
    public function checkRoleIsAllowed($resource, $role, $action)
    {
        if ($action == ActionEnum::CREATE) {

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

        } elseif ($action == ActionEnum::EDIT) {

            $pass = false;

            if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA || $role == UserRoleEnum::PROVEEDOR)
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

        } elseif ($action == ActionEnum::LIST) {

            $pass = false;

            if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA || $role == UserRoleEnum::PROVEEDOR)
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

    public function checkStatusWorkOrderIsQuoting($workOrder)
    {
        if($workOrder->work_order_status_id != WorkOrderStatus::STATUS_COTIZANDO)
        {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.work_order_quote.forbidden_because_wo_does_not_quoting'),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => AppObjectNameEnum::WORK_ORDER,
                    'field' => 'work_order_status_id'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkVendorDoesNotDuplicate($resource, $woID, $vendorID, $woqID, $field)
    {
        $data = $this->workOrderQuoteService->getByWorkOrderAndVendor($woID, $vendorID, $woqID);

        if($data){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.work_order_quote.vendor_already_exists'),
                'error_code' => ErrorCodesEnum::already_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    public function checkWorkOrderQuote($resource, $workOrderQuote)
    {
        if (!$workOrderQuote) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'work_order_quote']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return null;
    }

    public function checkNotification($resource, $value, $field)
    {
        $error_response = $this->checkIsInteger($resource, $value, $field);

        if($error_response)
            return $error_response;

        $validate = $value == QuoteNotificationEnum::NoRequiere || $value == QuoteNotificationEnum::Pendiente || $value == QuoteNotificationEnum::Enviada;

        if(!$validate)
        {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_valid', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::not_valid,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;

    }

    public function checkWorkOrderQuoteBelongsToUser($resource, $workOrderQuote, $user)
    {
        if($workOrderQuote->vendor_id != $user->vendor_id){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.work_order_quote.vendor_does_not_belong'),
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
