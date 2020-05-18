<?php
namespace App\Http\Controllers;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Services\WorkOrderQuoteFileService;
use App\Services\WorkOrderQuotePhotoService;
use Config;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacades;

class WorkOrderQuotePublicController extends Controller
{
    private $resource = AppObjectNameEnum::WORK_ORDER_QUOTE;
    private $path;
    private $workOrderQuotePhotoService;
    private $workOrderQuoteFileService;

    public function __construct(WorkOrderQuotePhotoService $workOrderQuotePhotoService, WorkOrderQuoteFileService $workOrderQuoteFileService)
    {
        $this->workOrderQuotePhotoService = $workOrderQuotePhotoService;
        $this->path = Config::get('app.path_woq_files');
        $this->workOrderQuoteFileService = $workOrderQuoteFileService;
    }

    public function photo($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $data = WorkOrderQuote::find($id);

            if (!$data) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Work Order Quote']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $photo = $this->workOrderQuotePhotoService->getByWorkOrderQuoteAndGuid($data->id, $guid, $photoName);

            if (!$photo) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid de la foto']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'guid'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($photoName);
            $path = $this->path . $data->id . '/' . $filename;

            if (file_exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $photoName . '"');

                $return = $response;
            }else{
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Imagen']),
                    'error_code' => ErrorCodesEnum::not_exists
                ], Response::HTTP_BAD_REQUEST);
            }

            return $return;

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

    }

    public function file($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $data = WorkOrderQuote::find($id);

            if (!$data) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Work Order Quote']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $file = $this->workOrderQuoteFileService->getByWorkOrderQuoteAndGuid($data->id, $guid);

            if (!$file) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid del archivo']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'guid'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $fName = $file->quote_file_name;
            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($fName);
            $path = $this->path . $data->id . '/' . $filename;

            if (file_exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $fName . '"');

                $return = $response;
            }else{
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Archivo']),
                    'error_code' => ErrorCodesEnum::not_exists
                ], Response::HTTP_BAD_REQUEST);
            }

            return $return;

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

    }

}
