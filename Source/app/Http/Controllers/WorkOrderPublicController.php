<?php
namespace App\Http\Controllers;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Models\WorkOrder\WorkOrder;
use App\Services\WorkOrderFileService;
use App\Services\WorkOrderPhotoService;
use App\Services\WorkOrderVideoService;
use Config;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacades;

class WorkOrderPublicController extends Controller
{
    private $resource = AppObjectNameEnum::WORK_ORDER;
    private $path;
    private $workOrderPhotoService;
    private $workOrderVideoService;
    private $workOrderFileService;

    public function __construct(WorkOrderPhotoService $workOrderPhotoService, WorkOrderVideoService $workOrderVideoService, WorkOrderFileService $workOrderFileService)
    {
        $this->workOrderPhotoService = $workOrderPhotoService;
        $this->path = Config::get('app.path_wo_files');
        $this->workOrderVideoService = $workOrderVideoService;
        $this->workOrderFileService = $workOrderFileService;
    }

    public function photo($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $data = WorkOrder::find($id);

            if (!$data) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Work Order']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $photo = $this->workOrderPhotoService->getByWorkOrderAndGuid($data->id, $guid);

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
            $photoName = $photo->name;
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

    public function video($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $data = WorkOrder::find($id);

            if (!$data) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Work Order']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $this->workOrderVideoService->getByWorkOrderAndGuid($data->id, $guid);

            if (!$data) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid del video']),
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
            $videoName = $data->video_name;
            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($videoName);
            $path = $this->path . $data->id . '/' . $filename;

            if (file_exists($path)) {
                $file = \File::get($path);
                $type = \File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $videoName . '"');

                $return = $response;
            }else{
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Video']),
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
            $data = WorkOrder::find($id);

            if (!$data) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Work Order']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $file = $this->workOrderFileService->getByWorkOrderAndGuid($data->id, $guid);

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
            $fName = $file->name;
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
