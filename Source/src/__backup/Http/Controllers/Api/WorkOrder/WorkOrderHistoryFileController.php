<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderHistoryFileService;
use App\Validations\WorkOrderHistoryFileValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderHistoryFileController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_HISTORY_FILE;
    protected $request;

    private $workOrderHistoryFileService;
    private $workOrderHistoryFileValidation;


    public function __construct(Request $request, WorkOrderHistoryFileValidation $workOrderHistoryFileValidation, WorkOrderHistoryFileService $workOrderHistoryFileService)
    {
        $this->request = $request;
        $this->workOrderHistoryFileService = $workOrderHistoryFileService;
        $this->workOrderHistoryFileValidation = $workOrderHistoryFileValidation;
    }

    public function store($woID, $wohID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderHistoryFileValidation->store($this->request, $this->resource, $woID, $wohID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderHistoryFileService->postProcess(
                $save,
                $this->workOrderHistoryFileValidation->workOrderHistory,
                $this->workOrderHistoryFileValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'work_order_history_id' => $this->workOrderHistoryFileValidation->workOrderHistory->id,
                    'success' => true
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}

