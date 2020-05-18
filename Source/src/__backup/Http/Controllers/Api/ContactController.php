<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Services\ContactService;
use App\Validations\ContactValidation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    private $resource = AppObjectNameEnum::CONTACT;
    private $request;
    private $contactService;
    private $contactValidation;

    public function __construct(Request $request, ContactService $contactService, ContactValidation $contactValidation)
    {
        $this->request = $request;
        $this->contactService = $contactService;
        $this->contactValidation = $contactValidation;
    }

    public function index()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->contactValidation->index($this->request, $this->resource, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->contactService->search(
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
