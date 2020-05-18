<?php
namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\ActionFileEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ArrayHelper;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderPhoto;
use App\Services\WorkOrderPhotoService;
use Illuminate\Http\Response;

class WorkOrderPhotoValidation extends BaseValidation
{
    public $user;
    public $workOrder;

    protected $workOrderValidation;
    protected $workOrderPhotoService;

    public function __construct(WorkOrderValidation $workOrderValidation, WorkOrderPhotoService $workOrderPhotoService)
    {
        $this->workOrderValidation = $workOrderValidation;
        $this->workOrderPhotoService = $workOrderPhotoService;
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

        $photos = $request->get('photos');
        $photosData = [];

        // ------------------------------------------------------------------
        // ======================= workOrder ================================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder(AppObjectNameEnum::WORK_ORDER, $workOrder);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ============================ Photos ==============================
        // ------------------------------------------------------------------

        // Check length "photos" key
        $error_response = $this->checkArrayLength($resource, $photos, 'photos', trans('api/validation.photos_no_one_key'));

        if($error_response)
            return $error_response;

        // Check Duplicates by order
        $photosToValidate = ArrayHelper::removeElementWithValue($photos, 'order', null);
        $error_response = $this->checkDuplicatesArrayByField($resource, $photosToValidate, 'order', 'order', trans('api/validation.duplicated', ['attribute' => 'order']));

        if ($error_response)
            return $error_response;

        // Check Photos
        foreach ($photos as $item_photo) {

            $action = (isset($item_photo['action']) ? StringHelper::Trim($item_photo['action']) : "");
            $order = (isset($item_photo['order']) ? StringHelper::Trim($item_photo['order']) : "");
            $guid = (isset($item_photo['guid']) ? StringHelper::Trim($item_photo['guid']) : "");
            $name = (isset($item_photo['name']) ? StringHelper::Trim($item_photo['name']) : "");
            $base64 = (isset($item_photo['base64']) ? StringHelper::Trim($item_photo['base64']) : "");

            //--------------------------------------------------
            // check action
            //--------------------------------------------------
            $error_response = $this->checkValueMustBeRequiredByPhoto($resource, $action, 'action', $name);

            if ($error_response)
                return $error_response;

            $error_response = $this->checkActionFileIsValidByPhoto($resource, $action, 'action', $name);

            if ($error_response)
                return $error_response;


            //------------------------------------------------------------------
            // Validations only for new or replace data
            //------------------------------------------------------------------
            if ($action == ActionFileEnum::CREATE) {

                $error_response = $this->checkOrderNumberIsValidByPhoto($resource, $order, 'order', $name);

                if ($error_response)
                    return $error_response;


                $error_response = $this->checkValueMustBeRequiredByPhoto($resource, $order, 'order', $name);

                if ($error_response)
                    return $error_response;


                $error_response = $this->checkValueMustBeRequiredByPhoto($resource, $base64, 'base64', $name);

                if ($error_response)
                    return $error_response;


                $error_response = $this->checkBase64StringIsImageValidByPhoto($resource, $base64, 'base64', $name);

                if ($error_response)
                    return $error_response;

                $error_response = $this->checkPhotoOrderExists($resource, $woID, $order, 'order', $name);

                if ($error_response)
                    return $error_response;


                // Set data
                $photo = [
                    'action' => $action,
                    'order' => $order,
                    'guid' => \FunctionHelper::CreateGUID(16),
                    'name' => $name,
                    'base64' => $base64,
                ];
                array_push($photosData, $photo);

            } else if($action == ActionFileEnum::EDIT) {

                $error_response = $this->checkOrderNumberIsValidByPhoto($resource, $order, 'order', $name);

                if ($error_response)
                    return $error_response;


                $error_response = $this->checkValueMustBeRequiredByPhoto($resource, $order, 'order', $name);

                if ($error_response)
                    return $error_response;


                $error_response = $this->checkValueMustBeRequiredByPhoto($resource, $base64, 'base64', $name);

                if ($error_response)
                    return $error_response;


                $error_response = $this->checkBase64StringIsImageValidByPhoto($resource, $base64, 'base64', $name);

                if ($error_response)
                    return $error_response;

                $customMessage = trans('api/validation.required_by_action', ['attribute' => 'guid', 'action' => $action]);
                $error_response = $this->checkValueMustBeRequiredWithCustomMessage($resource, $guid, 'guid', $customMessage);

                if ($error_response)
                    return $error_response;

                $error_response = $this->checkPhotoGuidExists($resource, $woID, $guid, 'guid', $name);

                if ($error_response)
                    return $error_response;

                $error_response = $this->checkPhotoOrderExists($resource, $woID, $order, 'order', $name, $guid);

                if ($error_response)
                    return $error_response;

                $workOrderPhoto = $this->workOrderPhotoService->getByWorkOrderAndGuid($woID, $guid);

                // Set data
                $photo = [
                    'action' => $action,
                    'order' => $order,
                    'guid' => $workOrderPhoto->guid,
                    'name' => $name,
                    'base64' => $base64,
                ];
                array_push($photosData, $photo);

            } else if($action == ActionFileEnum::DELETE) {

                $customMessage = trans('api/validation.required_by_action', ['attribute' => 'guid', 'action' => $action]);
                $error_response = $this->checkValueMustBeRequiredWithCustomMessage($resource, $guid, 'guid', $customMessage);

                if ($error_response)
                    return $error_response;

                $error_response = $this->checkPhotoGuidExists($resource, $woID, $guid, 'guid', $name);


                if ($error_response)
                    return $error_response;

                $workOrderPhoto = $this->workOrderPhotoService->getByWorkOrderAndGuid($woID, $guid);

                // Set data
                $photo = [
                    'action' => $action,
                    'order' => null,
                    'guid' => $workOrderPhoto->guid,
                    'name' => null,
                    'base64' => null,
                ];
                array_push($photosData, $photo);
            }
        }

        $save = $photosData;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        $this->workOrder = $workOrder;

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

    public function checkPhotoGuidExists($resource, $workOrderID, $guid, $field, $photoName)
    {
        $data = $this->workOrderPhotoService->getByWorkOrderAndGuid($workOrderID, $guid);

        if($data == null){

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => $field.': ' . $guid]),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                    'photo_name' => $photoName
                ]
            ], Response::HTTP_NOT_FOUND);

        }

        return null;
    }

    public function checkPhotoOrderExists($resource, $workOrderID, $order, $field, $photoName, $guid = null)
    {
        $data =  $this->workOrderPhotoService->getPhotoOrderExists($workOrderID, $order, $guid);

        if($data != null){

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.already_exists', ['attribute' => $field]),
                'error_code' => ErrorCodesEnum::already_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => $field,
                    'photo_name' => $photoName
                ]
            ], Response::HTTP_BAD_REQUEST);

        }

        return null;
    }

}
