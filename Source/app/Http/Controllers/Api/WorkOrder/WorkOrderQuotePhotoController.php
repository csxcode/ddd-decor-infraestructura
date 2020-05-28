<?php
namespace App\Http\Controllers\Api\WorkOrder;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\WorkOrderQuotePhotoService;
use App\Validations\WorkOrderQuotePhotoValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderQuotePhotoController extends Controller
{
    protected $resource = AppObjectNameEnum::WORK_ORDER_QUOTE_PHOTO;
    protected $request;
    protected $workOrderQuotePhotoService;
    protected $workOrderQuotePhotoValidation;

    public function __construct(Request $request, WorkOrderQuotePhotoService $workOrderQuotePhotoService, WorkOrderQuotePhotoValidation $workOrderQuotePhotoValidation)
    {
        $this->request = $request;
        $this->workOrderQuotePhotoService = $workOrderQuotePhotoService;
        $this->workOrderQuotePhotoValidation = $workOrderQuotePhotoValidation;
    }

    public function store($woID, $woqID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuotePhotoValidation->store($this->request, $this->resource, $woID, $woqID, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderQuotePhotoService->postProcess(
                $save,
                $this->workOrderQuotePhotoValidation->workOrderQuote,
                $this->workOrderQuotePhotoValidation->user
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_QUOTE_PHOTO,
                    'work_order_quote_id' => $this->workOrderQuotePhotoValidation->workOrderQuote->id,
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

    public function destroy ($woID, $woqID)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->workOrderQuotePhotoValidation->destroy($this->request, $this->resource, $woID, $woqID, $data);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->workOrderQuotePhotoService->delete(
                $this->workOrderQuotePhotoValidation->workOrderQuote,
                $this->workOrderQuotePhotoValidation->user,
                $data
            );
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::WORK_ORDER_QUOTE_PHOTO,
                    'work_order_quote_id' => $this->workOrderQuotePhotoValidation->workOrderQuote->id,
                    'success' => true
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}

