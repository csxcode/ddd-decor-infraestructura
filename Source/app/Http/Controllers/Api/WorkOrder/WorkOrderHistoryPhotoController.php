<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderHistoryPhotoService;
use App\Validations\WorkOrderHistoryPhotoValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderHistoryPhotoController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_HISTORY_PHOTO;
    protected $request;

    private $workOrderHistoryPhotoValidation;
    private $workOrderHistoryPhotoService;

    public function __construct(Request $request, WorkOrderHistoryPhotoValidation $workOrderHistoryPhotoValidation, WorkOrderHistoryPhotoService $workOrderHistoryPhotoService)
    {
        $this->request = $request;
        $this->workOrderHistoryPhotoValidation = $workOrderHistoryPhotoValidation;
        $this->workOrderHistoryPhotoService = $workOrderHistoryPhotoService;
    }

    public function store($woID, $wohID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderHistoryPhotoValidation->store($this->request, $this->resource, $woID, $wohID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderHistoryPhotoService->postProcess(
                $save,
                $this->workOrderHistoryPhotoValidation->workOrderHistory,
                $this->workOrderHistoryPhotoValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'work_order_history_id' => $this->workOrderHistoryPhotoValidation->workOrderHistory->id,
                    'response' => [
                        'message' => 'Fotos procesadas correctamente.',
                        'photos_processed' => count($save)
                    ]
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}

