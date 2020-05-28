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

class WorkOrderQuotePhotoValidation extends BaseValidation
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

        $photo1_name =  StringHelper::Trim($request->get('photo1_name'));
        $photo1_base64 = StringHelper::Trim($request->get('photo1_base64'));
        $photo2_name = StringHelper::Trim($request->get('photo2_name'));
        $photo2_base64 = StringHelper::Trim($request->get('photo2_base64'));
        $photo3_name = StringHelper::Trim($request->get('photo3_name'));
        $photo3_base64 = StringHelper::Trim($request->get('photo3_base64'));

        $photos = [];

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
        // ============================ Photos ==============================
        // ------------------------------------------------------------------

        // Photo 1
        if($request->exists('photo1_name') && $request->exists('photo1_base64')) {
            array_push($photos, [
                'order' => 1, 'name' => $photo1_name, 'base64' => $photo1_base64
            ]);
        }

        // Photo 2
        if($request->exists('photo2_name') && $request->exists('photo2_base64')) {
            array_push($photos, [
                'order' => 2, 'name' => $photo2_name, 'base64' => $photo2_base64
            ]);
        }

        // Photo 3
        if($request->exists('photo3_name') && $request->exists('photo3_base64')) {
            array_push($photos, [
                'order' => 3, 'name' => $photo3_name, 'base64' => $photo3_base64
            ]);
        }

        foreach ($photos as $item)
        {
            $keyName = 'photo' . $item['order'] . '_name';
            $keyBase64 = 'photo' . $item['order'] . '_base64';

            $error_response = $this->checkValueMustBeRequired($resource, $item['name'], $keyName);

            if($error_response)
                return $error_response;

            $error_response = $this->checkValueMustBeRequired($resource, $item['base64'], $keyBase64);

            if($error_response)
                return $error_response;

            $error_response = $this->checkBase64StringIsValid($resource, $item['base64'], $keyBase64);

            if($error_response)
                return $error_response;

            $error_response = $this->checkBase64StringIsImageValid($resource, $item['base64'], $keyBase64);

            if($error_response)
                return $error_response;

        }

        $arrayFields = ['photo1_name', 'photo1_base64', 'photo2_name', 'photo2_base64', 'photo3_name', 'photo3_base64'];
        $error_response = $this->checkArrayLength($resource, $photos, $arrayFields, trans('api/validation.photos_no_one_key'));

        if($error_response)
            return $error_response;

        $save = $photos;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }

    public function destroy($request, $resource, $woID, $woqID, &$data)
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

        $photo_guid = StringHelper::Trim($request->get('guid'));


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
        // ======================= photo_guid ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $photo_guid, 'guid');

        if($error_response)
            return $error_response;

        $error_response = $this->checkPhotoGuidExists($resource, $workOrderQuote->id, $photo_guid, 'guid');

        if($error_response)
            return $error_response;


        $data = $this->getOrderPhoto($workOrderQuote->id, $photo_guid);

        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;
        $this->workOrderQuote = $workOrderQuote;

        return null;
    }


    // ------------------------------------------------------------------
    // ====================== Private Functions =========================
    // ------------------------------------------------------------------
    private function getWorkOrderQuotePhoto($id, $guid)
    {
        return WorkOrderQuote::where('id', $id)
            ->where('photo1_guid', $guid)
            ->orWhere('photo2_guid', $guid)
            ->orWhere('photo3_guid', $guid)
            ->first();
    }

    private function getOrderPhoto($workOrderQuoteID, $guid)
    {
        $return = null;

        $data = $this->getWorkOrderQuotePhoto($workOrderQuoteID, $guid);

        if($data != null) {
            $return = [];

            if ($data->photo1_guid == $guid) {
                $return['order'] = 1;
                $return['name'] = $data->photo1_name;
                $return['guid'] = $data->photo1_guid;

            } else if ($data->photo2_guid == $guid) {
                $return['order'] = 2;
                $return['name'] = $data->photo2_name;
                $return['guid'] = $data->photo2_guid;

            } else if ($data->photo3_guid == $guid) {
                $return['order'] = 3;
                $return['name'] = $data->photo3_name;
                $return['guid'] = $data->photo3_guid;
            }
        }

        return $return;
    }


    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------

    public function checkPhotoGuidExists($resource, $workOrderQuoteID, $guid, $field)
    {
        $dataFound = $this->getWorkOrderQuotePhoto($workOrderQuoteID, $guid);

        if($dataFound == null){

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
