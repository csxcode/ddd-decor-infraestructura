<?php

namespace App\Http\Controllers;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\CheckList\Validations\ChecklistItemDetailsValidation;
use App\Models\Checklist\Checklist;
use App\Models\Checklist\ChecklistItemDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as ResponseFacades;

class ChecklistPhotoController extends Controller
{
    protected $resource = AppObjectNameEnum::CHECKLIST;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function GetPhoto($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $checklist = Checklist::find($id);

            // [Checklist]
            if (!$checklist) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $checklist_photo = ChecklistItemDetail::where('checklist_id', $checklist->id)
                ->where('photo1_guid', $guid)
                ->orWhere('photo2_guid', $guid)
                ->orWhere('photo3_guid', $guid)
                ->first();

            if (!$checklist_photo) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid de la foto del checklist']),
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
            $photo_name = null;

            if($checklist_photo->photo1_guid == $guid) {
                $photo_name = $checklist_photo->photo1_name;

            }else if($checklist_photo->photo2_guid == $guid){
                $photo_name = $checklist_photo->photo2_name;

            }else if($checklist_photo->photo3_guid == $guid){
                $photo_name = $checklist_photo->photo3_name;
            }

            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($photo_name);
            $path = Config::get('app.path_checklist_photos') . $checklist->id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $photo_name . '"');

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

    public function GetVideo($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $checklist = Checklist::find($id);

            // [Checklist]
            if (!$checklist) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            foreach(ChecklistItemDetailsValidation::$video_ext as $ext){
                $_ext = '.' . $ext;
                $guid = str_replace($_ext, '', $guid);
            }

            $guid = StringHelper::Trim($guid);

            $checklist_photo = ChecklistItemDetail::where('checklist_id', $checklist->id)
                ->where('video_guid', $guid)
                ->first();

            if (!$checklist_photo) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid del video del checklist']),
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
            $video_name = $checklist_photo->video_name;

            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($video_name);
            $path = Config::get('app.path_checklist_photos') . $checklist->id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $video_name . '"');

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
}
