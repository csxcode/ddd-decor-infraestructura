<?php

namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\ErrorHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderSearchViewService;
use App\Services\WorkOrderService;
use App\Transformers\WorkOrderTransformer;
use App\Validations\WorkOrderValidation;
use Illuminate\Http\Response;

class WorkOrderController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER;
    protected $request;
    protected $workOrder;
    protected $workOrderService;
    protected $workOrderSearchViewService;
    protected $workOrderValidation;

    public function __construct(Request $request, WorkOrder $workOrder, WorkOrderService $workOrderService, WorkOrderSearchViewService $workOrderSearchViewService,
        WorkOrderValidation $workOrderValidation)
    {
        $this->request = $request;
        $this->workOrder = $workOrder;
        $this->workOrderService = $workOrderService;
        $this->workOrderSearchViewService = $workOrderSearchViewService;
        $this->workOrderValidation = $workOrderValidation;
    }

    public function store()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderValidation->store($this->request, $this->resource, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();

            $params = [
                'required_days' => $save['required_days'],
                'work_specs' => $save['work_specs'],
                'branch_location_id' => $save['branch_location_id'],
                'major_account_id' => $save['major_account_id'],
                'sap_description' => $save['sap_description'],
            ];

            $data = $this->workOrderService->create($params, $this->workOrderValidation->user);

            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $data->id,
                    'wo_number' => $data->wo_number,
                    'created_at' => DatetimeHelper::TransformToTimeStamp($data->created_at)
                ],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function update($id)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderValidation->update(
                $this->request, $this->resource, $id, $changes);

            if ($error_response)
                return $error_response;


            // --------------------------------------------
            // Save
            // --------------------------------------------
            $user = $this->workOrderValidation->user;
            $workOrder = $this->workOrderValidation->workOrder;

            // update only if there are changes
            if ($changes) {

                \DB::beginTransaction();
                $workOrder = $this->workOrderService->update($workOrder, $user);
                \DB::commit();

            } else {
                return ErrorHelper::sendResponseNoChanges($this->resource, $workOrder->id);
            }


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $workOrder->id,
                    'wo_number' => $workOrder->wo_number,
                    'updated_at' => DatetimeHelper::TransformToTimeStamp($workOrder->updated_at)
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function show($id)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderValidation->show($this->request, $this->resource, $id);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                (new WorkOrderTransformer)->show(AppObjectNameEnum::WORK_ORDER, $this->workOrderValidation->workOrder),
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function index()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderValidation->index($this->request, $this->resource, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderSearchViewService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'wo_number', 'direction' => 'desc']
            );


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                (new WorkOrderTransformer)->index($this->resource, 'data', $data),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
