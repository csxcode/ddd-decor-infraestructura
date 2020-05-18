<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderHistoryVideoService;
use App\Validations\WorkOrderHistoryVideoValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderHistoryVideoController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_HISTORY_VIDEO;
    protected $request;

    private $workOrderHistoryVideoValidation;
    private $workOrderHistoryVideoService;


    public function __construct(Request $request, WorkOrderHistoryVideoValidation $workOrderHistoryVideoValidation, WorkOrderHistoryVideoService $workOrderHistoryVideoService)
    {
        $this->request = $request;
        $this->workOrderHistoryVideoValidation = $workOrderHistoryVideoValidation;
        $this->workOrderHistoryVideoService = $workOrderHistoryVideoService;
    }

    public function store($woID, $wohID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderHistoryVideoValidation->store($this->request, $this->resource, $woID, $wohID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderHistoryVideoService->postProcess(
                $save,
                $this->workOrderHistoryVideoValidation->workOrderHistory,
                $this->workOrderHistoryVideoValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'work_order_history_id' => $this->workOrderHistoryVideoValidation->workOrderHistory->id,
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

