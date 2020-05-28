<?php
namespace App\Validations;

use App\Enums\AppObjectNameEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderHistoryService;

class WorkOrderHistoryPhotoValidation extends BaseValidation
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
        $workOrderHistory = $this->workOrderHistoryService->findByWorkOrder($wohID, $woID);

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
        // ======================= workOrderHistory ===========================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueHasData(AppObjectNameEnum::WORK_ORDER_HISTORY, $workOrderHistory, 'work_order_history_id');

        if($error_response)
            return $error_response;


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
        $this->workOrderHistory = $workOrderHistory;

        return null;
    }

}
