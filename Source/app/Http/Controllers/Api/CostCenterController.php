<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Services\CostCenterService;
use App\Validations\CostCenterValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class CostCenterController extends Controller
{
    protected $resource = AppObjectNameEnum::COST_CENTER;
    protected $request;
    protected $costCenterService;
    protected $costCenterValidation;

    public function __construct(Request $request, CostCenterService $costCenterService, CostCenterValidation $costCenterValidation)
    {
        $this->request = $request;
        $this->costCenterService = $costCenterService;
        $this->costCenterValidation = $costCenterValidation;
    }

    public function index()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->costCenterValidation->index($this->request, $this->resource, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->costCenterService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'name', 'direction' => 'asc']
            );


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json([
                'object' => $this->resource,
                'data' => $data
            ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
