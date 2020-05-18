<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\PaginateHelper;
use App\Services\MajorAccountService;
use App\Validations\MajorAccountValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class MajorAccountController extends Controller
{
    protected $resource = AppObjectNameEnum::MAJOR_ACCOUNT;
    protected $request;
    protected $majorAccountService;
    protected $majorAccountValidation;

    public function __construct(Request $request, MajorAccountService $majorAccountService, MajorAccountValidation $majorAccountValidation)
    {
        $this->request = $request;
        $this->majorAccountService = $majorAccountService;
        $this->majorAccountValidation = $majorAccountValidation;
    }

    public function index()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->majorAccountValidation->index($this->request, $this->resource, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->majorAccountService->search(
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
