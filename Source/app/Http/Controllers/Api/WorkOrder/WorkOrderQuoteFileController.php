<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderQuoteFileService;
use App\Validations\WorkOrderQuoteFileValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderQuoteFileController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_QUOTE_FILE;
    protected $request;
    protected $workOrderQuoteFileService;
    protected $workOrderQuoteFileValidation;

    public function __construct(Request $request, WorkOrderQuoteFileService $workOrderQuoteFileService, WorkOrderQuoteFileValidation $workOrderQuoteFileValidation)
    {
        $this->request = $request;
        $this->workOrderQuoteFileService = $workOrderQuoteFileService;
        $this->workOrderQuoteFileValidation = $workOrderQuoteFileValidation;
    }

    public function store($woID, $woqID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuoteFileValidation->store($this->request, $this->resource, $woID, $woqID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderQuoteFileService->postProcess(
                $save,
                $this->workOrderQuoteFileValidation->workOrderQuote,
                $this->workOrderQuoteFileValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_QUOTE_FILE,
                    'work_order_quote_id' => $this->workOrderQuoteFileValidation->workOrderQuote->id,
                    'success' => true
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
            $error_response = $this->workOrderQuoteFileValidation->destroy($this->request, $this->resource, $woID, $woqID);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderQuoteFileService->delete(
                $this->workOrderQuoteFileValidation->workOrderQuote,
                $this->workOrderQuoteFileValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_QUOTE_FILE,
                    'work_order_quote_id' => $this->workOrderQuoteFileValidation->workOrderQuote->id,
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

