<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Services\CostCenterService;
use App\Services\VendorService;
use App\Validations\CostCenterValidation;
use App\Validations\VendorValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class VendorController extends Controller
{
    private $resource = AppObjectNameEnum::VENDOR;
    private $request;
    private $vendorService;
    private $vendorValidation;

    public function __construct(Request $request, VendorService $vendorService, VendorValidation $vendorValidation)
    {
        $this->request = $request;
        $this->vendorService = $vendorService;
        $this->vendorValidation = $vendorValidation;
    }

    public function index()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->vendorValidation->index($this->request, $this->resource, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->vendorService->search(
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
