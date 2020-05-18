<?php
namespace App\Http\Controllers\Api;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Http\Controllers\Controller;
use App\Services\MaintenanceService;
use App\Transformers\MaintenanceTransformer;
use App\UseCases\Maintenance\ApiSearch;
use App\Validations\MaintenanceValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MaintenanceController extends Controller
{
    private $resource = AppObjectNameEnum::MAINTENANCE;

    private $request;
    private $maintenanceValidation;
    private $maintenanceService;

    public function __construct(Request $request, MaintenanceValidation $maintenanceValidation, MaintenanceService $maintenanceService)
    {
        $this->request = $request;
        $this->maintenanceValidation = $maintenanceValidation;
        $this->maintenanceService = $maintenanceService;
    }

    public function store()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->maintenanceValidation->store($this->request, $this->resource, $save);

            if ($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            $user = $this->maintenanceValidation->user;

            \DB::beginTransaction();
            $data = $this->maintenanceService->store($save, $user);
            \DB::commit();


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $data->id
                ],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function update($id)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->maintenanceValidation->update($this->request, $this->resource, $id, $changes);

            if ($error_response)
                return $error_response;


            // --------------------------------------------
            // Save
            // --------------------------------------------
            $maintenance = $this->maintenanceValidation->maintenance;
            $user = $this->maintenanceValidation->user;

            // update only if there are changes
            if ($changes) {
                \DB::beginTransaction();
                $this->maintenanceService->update($maintenance, $user);
                \DB::commit();
            } else {
                return ErrorHelper::sendResponseNoChanges($this->resource, $maintenance->id);
            }


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $maintenance->id,
                    'updated_at' => \DatetimeHelper::TransformToTimeStamp($maintenance->updated_at)
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            \DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function destroy($id)
    {
        try {

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->maintenanceValidation->destroy($this->request, $this->resource, $id);

            if ($error_response)
                return $error_response;


            // --------------------------------------------
            // Delete
            // --------------------------------------------
            \DB::beginTransaction();
            $this->maintenanceService->destroy($this->maintenanceValidation->maintenance);
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

    public function show($id)
    {
        try {

            // Validations
            $error_response = $this->maintenanceValidation->show($this->request, $this->resource, $id);

            if ($error_response)
                return $error_response;

            // Return Data
            $user = $this->maintenanceValidation->user;
            $data = $this->maintenanceService->show($id, $user);

            return response()->json(
                (new MaintenanceTransformer)->show(AppObjectNameEnum::MAINTENANCE, $data),
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function index()
    {
        try {

            // Validations
            $error_response = $this->maintenanceValidation->index($this->request, $this->resource, $params);

            if ($error_response)
                return $error_response;

            // Get Data
            $data = (new ApiSearch())->search($params);

            // Return Data
            return response()->json(
                (new MaintenanceTransformer())->index($this->resource, 'data', $data),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
