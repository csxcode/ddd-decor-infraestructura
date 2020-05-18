<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderQuoteService;
use App\Transformers\WorkOrderQuoteTransformer;
use App\Validations\WorkOrderQuoteValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderQuoteController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_QUOTE;
    protected $request;
    protected $workOrderQuoteService;
    protected $workOrderQuoteValidation;

    public function __construct(Request $request, WorkOrderQuoteService $workOrderQuoteService, WorkOrderQuoteValidation $workOrderQuoteValidation)
    {
        $this->request = $request;
        $this->workOrderQuoteService = $workOrderQuoteService;
        $this->workOrderQuoteValidation = $workOrderQuoteValidation;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuoteValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderQuoteService->massCreate($save, $this->workOrderQuoteValidation->user);
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

    public function update($woID, $woqID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuoteValidation->update(
                $this->request, $this->resource, $woID, $woqID, $changes);

            if ($error_response)
                return $error_response;


            // --------------------------------------------
            // Save
            // --------------------------------------------
            $user = $this->workOrderQuoteValidation->user;
            $workOrderQuote = $this->workOrderQuoteValidation->workOrderQuote;

            // update only if there are changes
            if ($changes) {
                \DB::beginTransaction();
                $this->workOrderQuoteService->update($workOrderQuote, $user);
                \DB::commit();
            } else {
                return ErrorHelper::sendResponseNoChanges($this->resource, $workOrderQuote->id);
            }


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $workOrderQuote->id,
                    'updated_at' => DatetimeHelper::TransformToTimeStamp($workOrderQuote->updated_at)
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function destroy ($woID, $woqID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuoteValidation->destroy($this->request, $this->resource, $woID, $woqID);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderQuoteService->delete($this->workOrderQuoteValidation->workOrderQuote);
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response([
                'object' => $this->resource,
                'success' => true
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function index($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuoteValidation->index($this->request, $this->resource, $woID, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderQuoteService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'woq_id', 'direction' => 'desc']
            );


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                (new WorkOrderQuoteTransformer)->index($this->resource, 'data', $data),
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
            $error_response = $this->workOrderQuoteValidation->show($this->request, $this->resource, $woID, $id);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                (new WorkOrderQuoteTransformer)->show(AppObjectNameEnum::WORK_ORDER_QUOTE, $this->workOrderQuoteValidation->workOrderQuote),
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}

