<?php

namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderHistoryService;
use App\Transformers\WorkOrderHistoryTransformer;
use App\Validations\WorkOrderHistoryValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderHistoryController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_HISTORY;
    protected $request;

    private $workOrderHistoryValidation;
    private $workOrderHistoryService;

    public function __construct(Request $request, WorkOrderHistoryValidation $workOrderHistoryValidation, WorkOrderHistoryService $workOrderHistoryService)
    {
        $this->request = $request;
        $this->workOrderHistoryValidation = $workOrderHistoryValidation;
        $this->workOrderHistoryService = $workOrderHistoryService;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderHistoryValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $data = $this->workOrderHistoryService->store($save, $this->workOrderHistoryValidation->user);
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $data->id
                ],
                Response::HTTP_CREATED
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
            $error_response = $this->workOrderHistoryValidation->index($this->request, $this->resource, $woID, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderHistoryService->search(
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

    public function show($woID, $id)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderHistoryValidation->show($this->request, $this->resource, $woID, $id);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                (new WorkOrderHistoryTransformer)->show($this->resource, $this->workOrderHistoryValidation->workOrderHistory),
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
