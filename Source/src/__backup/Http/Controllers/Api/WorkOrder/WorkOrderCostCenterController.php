<?php

namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderCostCenterService;
use App\Validations\WorkOrderCostCenterValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderCostCenterController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_COST_CENTER;
    protected $request;
    protected $workOrderCostCenterService;
    protected $workOrderCostCenterValidation;

    public function __construct(Request $request, WorkOrderCostCenterService $workOrderCostCenterService, WorkOrderCostCenterValidation $workOrderCostCenterValidation)
    {
        $this->request = $request;
        $this->workOrderCostCenterService = $workOrderCostCenterService;
        $this->workOrderCostCenterValidation = $workOrderCostCenterValidation;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderCostCenterValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderCostCenterService->massCreate($save);
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'success' => true
                ],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function destroy($woID, $code)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderCostCenterValidation->destroy($this->request, $this->resource, $woID, $code);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderCostCenterService->delete($this->workOrderCostCenterValidation->workOrderCostCenter);
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response([
                'object' => $this->resource,
                'success' => true
            ], Response::HTTP_OK);

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
            $error_response = $this->workOrderCostCenterValidation->index($this->request, $this->resource, $woID, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderCostCenterService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'id', 'direction' => 'desc']
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
}
