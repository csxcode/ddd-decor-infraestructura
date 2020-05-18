<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 2:51 PM
 */

namespace App\Http\Controllers\Api;

use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Services\MailService;
use App\Services\StoreService;
use App\Transformers\StoreTransformer;
use App\Validations\StoreValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreController extends Controller
{
    private $resource = AppObjectNameEnum::STORE;
    private $request;
    private $storeService;
    private $storeValidation;

    public function __construct(Request $request, StoreService $storeService, StoreValidation $storeValidation)
    {
        $this->request = $request;
        $this->storeService = $storeService;
        $this->storeValidation = $storeValidation;
    }

    public function index()
    {
        try {
            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->storeValidation->index($this->request, $this->resource, $params);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $this->storeService->search(
                AccessTypeEnum::Api,
                $params,
                ['sort' => 'name', 'direction' => 'asc']
            );


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                (new StoreTransformer)->index($this->resource, 'data', $data),
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

}
