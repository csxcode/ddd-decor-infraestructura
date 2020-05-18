<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderPhotoService;
use App\Validations\WorkOrderPhotoValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderPhotoController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_PHOTO;
    protected $request;
    protected $workOrderPhotoService;
    protected $workOrderPhotoValidation;

    public function __construct(Request $request, WorkOrderPhotoService $workOrderPhotoService, WorkOrderPhotoValidation $workOrderPhotoValidation)
    {
        $this->request = $request;
        $this->workOrderPhotoService = $workOrderPhotoService;
        $this->workOrderPhotoValidation = $workOrderPhotoValidation;
    }

    public function store($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderPhotoValidation->store($this->request, $this->resource, $woID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderPhotoService->postProcess(
                $save,
                $this->workOrderPhotoValidation->workOrder->id
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_PHOTO,
                    'work_order_id' => $this->workOrderPhotoValidation->workOrder->id,
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

    public function index($woID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderPhotoValidation->index($this->request, $this->resource, $woID, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->workOrderPhotoService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'order', 'direction' => 'asc']
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

