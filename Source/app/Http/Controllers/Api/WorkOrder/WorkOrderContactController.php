<?php

namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderContactService;
use App\Transformers\WorkOrderContactTransformer;
use App\Validations\WorkOrderContactValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderContactController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_CONTACT;
    protected $request;
    protected $workOrderContactService;
    protected $workOrderContactValidation;

    public function __construct(Request $request, WorkOrderContactService $workOrderContactService, WorkOrderContactValidation $workOrderContactValidation)
    {
        $this->request = $request;
        $this->workOrderContactService = $workOrderContactService;
        $this->workOrderContactValidation = $workOrderContactValidation;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderContactValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderContactService->massCreate($save);
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

    public function destroy($woID, $contactId)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderContactValidation->destroy($this->request, $this->resource, $woID, $contactId);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderContactService->delete($this->workOrderContactValidation->workOrderContact);
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
            $error_response = $this->workOrderContactValidation->index($this->request, $this->resource, $woID, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderContactService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'woc_id', 'direction' => 'desc']
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
