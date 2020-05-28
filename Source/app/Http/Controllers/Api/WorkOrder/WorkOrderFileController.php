<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderFileService;
use App\Validations\WorkOrderFileValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderFileController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_FILE;
    protected $request;
    protected $workOrderFileService;
    protected $workOrderFileValidation;

    public function __construct(Request $request, WorkOrderFileService $WorkOrderFileService, WorkOrderFileValidation $workOrderFileValidation)
    {
        $this->request = $request;
        $this->workOrderFileService = $WorkOrderFileService;
        $this->workOrderFileValidation = $workOrderFileValidation;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderFileValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $data = $this->workOrderFileService->postProcess(
                $save,
                $this->workOrderFileValidation->workOrder->id
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_FILE,
                    'work_order_id' => $this->workOrderFileValidation->workOrder->id,
                    'id' => $data->id,
                    'success' => true
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function index($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderFileValidation->index($this->request, $this->resource, $woID, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderFileService->search(
                AccessTypeEnum::Api,
                $params
            );


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                PaginateHelper::TransformPaginateData($this->resource, 'data', $data),
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function destroy ($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderFileValidation->destroy($this->request, $this->resource, $woID);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderFileService->delete(
                $this->workOrderFileValidation->workOrderFile
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_FILE,
                    'work_order_id' => $this->workOrderFileValidation->workOrder->id,
                    'response' => [
                        'message' => 'Archivo eliminado.'
                    ]
                ],
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}

