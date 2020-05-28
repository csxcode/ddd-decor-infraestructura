<?php
namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Services\WorkOrderQuoteService;
use Illuminate\Http\Response;

class WorkOrderQuoteFileValidation extends BaseValidation
{
    public $user;
    public $workOrder;
    public $workOrderQuote;

    protected $workOrderValidation;
    protected $workOrderQuoteValidation;
    private $workOrderQuoteService;

    public function __construct(WorkOrderValidation $workOrderValidation, WorkOrderQuoteValidation $workOrderQuoteValidation, WorkOrderQuoteService $workOrderQuoteService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderQuoteValidation = $workOrderQuoteValidation;
        $this->workOrderQuoteService = $workOrderQuoteService;
    }

    public function store($request, $resource, $woID, $woqID, &$save)
    {

        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderQuote = $this->workOrderQuoteService->findByWorkOrder($woqID, $woID);

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
        // ======================= workOrderQuote ===========================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderQuoteValidation->checkWorkOrderQuote(AppObjectNameEnum::WORK_ORDER_QUOTE, $workOrderQuote);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =============== by status of work_order ==========================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderQuoteValidation->checkStatusWorkOrderIsQuoting($workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderQuoteValidation->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // =================== Rules for proovedor ==========================
        // ------------------------------------------------------------------
        if ($role == UserRoleEnum::PROVEEDOR)
        {
            // ------------------------------------------------------------------------------------------
            // El usuario "Proveedor" puede actualizar solo si la cotización le pertenece al proveedor
            // ------------------------------------------------------------------------------------------
            $error_response = $this->workOrderQuoteValidation->checkWorkOrderQuoteBelongsToUser($resource, $workOrderQuote, $user);

            if($error_response)
                return $error_response;
        }

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
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }

    public function destroy($request, $resource, $woID, $woqID)
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
        $workOrderQuote = $this->workOrderQuoteService->findByWorkOrder($woqID, $woID);

        $file_guid = StringHelper::Trim($request->get('guid'));


        // ------------------------------------------------------------------
        // ======================= workOrder ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ======================= workOrderQuote ===========================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderQuoteValidation->checkWorkOrderQuote(AppObjectNameEnum::WORK_ORDER_QUOTE, $workOrderQuote);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =============== by status of work_order ==========================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderQuoteValidation->checkStatusWorkOrderIsQuoting($workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderQuoteValidation->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // =================== Rules for proovedor ==========================
        // ------------------------------------------------------------------
        if ($role == UserRoleEnum::PROVEEDOR)
        {
            // ------------------------------------------------------------------------------------------
            // El usuario "Proveedor" puede actualizar solo si la cotización le pertenece al proveedor
            // ------------------------------------------------------------------------------------------
            $error_response = $this->workOrderQuoteValidation->checkWorkOrderQuoteBelongsToUser($resource, $workOrderQuote, $user);

            if($error_response)
                return $error_response;
        }

        // ------------------------------------------------------------------
        // ======================= file_guid ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $file_guid, 'guid');

        if($error_response)
            return $error_response;

        $error_response = $this->checkFileGuidExists($resource, $workOrderQuote->id, $file_guid, 'guid');

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }


    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    public function checkFileGuidExists($resource, $woqID, $fileGuid, $field)
    {
        $count = WorkOrderQuote::where('id', $woqID)->where('quote_file_guid', $fileGuid)->count();

        if($count == 0){

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => $field]),
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
