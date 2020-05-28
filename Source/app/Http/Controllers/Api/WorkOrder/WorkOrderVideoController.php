<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderVideoService;
use App\Validations\WorkOrderVideoValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderVideoController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER;
    protected $request;

    private $workOrderVideoValidation;
    private $workOrderVideoService;


    public function __construct(Request $request, WorkOrderVideoValidation $workOrderVideoValidation, WorkOrderVideoService $workOrderVideoService)
    {
        $this->request = $request;
        $this->workOrderVideoValidation = $workOrderVideoValidation;
        $this->workOrderVideoService = $workOrderVideoService;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderVideoValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderVideoService->postProcess(
                $save,
                $this->workOrderVideoValidation->workOrder,
                $this->workOrderVideoValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'work_order_id' => $this->workOrderVideoValidation->workOrder->id,
                    'success' => true
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function destroy ($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderVideoValidation->destroy($this->request, $this->resource, $woID);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderVideoService->delete(
                $this->workOrderVideoValidation->workOrder,
                $this->workOrderVideoValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER,
                    'work_order_id' => $this->workOrderVideoValidation->workOrder->id,
                    'success' => true
                ],
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

}

