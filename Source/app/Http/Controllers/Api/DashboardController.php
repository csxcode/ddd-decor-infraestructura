<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStoreBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $resource = AppObjectNameEnum::STORE;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function Get()
    {
        try {
            // -----------------------------------------------------
            // Return and get data
            // -----------------------------------------------------
            $user = User::GetByToken($this->request->bearerToken());
            $data = DB::select(DB::raw('CALL sp_get_data_dashboard('.$user->user_id.')'));

            return response()->json(
                [
                    'object' => AppObjectNameEnum::DASHBOARD,
                    'data' => $data[0]
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

}