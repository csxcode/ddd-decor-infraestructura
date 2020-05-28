<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 5/30/2019
 * Time: 4:39 PM
 */

namespace App\Http\Controllers\Api\Ticket;


use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketStatus;
use Illuminate\Http\Response;

class TicketStatusController extends Controller
{
    public function index()
    {
        try {
            $data = TicketStatus::select('id', 'name')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_STATUS,
                    'status' => $data
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

}