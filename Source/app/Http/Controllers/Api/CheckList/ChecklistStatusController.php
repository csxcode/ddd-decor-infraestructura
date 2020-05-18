<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 5/28/2019
 * Time: 11:36 AM
 */

namespace App\Http\Controllers\Api\CheckList;


use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Checklist\ChecklistStatus;
use Illuminate\Http\Response;

class ChecklistStatusController extends Controller
{
    public function All()
    {
        try {
            $data = ChecklistStatus::select('id', 'name')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST_STATUS,
                    'checklist_status' => $data
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}