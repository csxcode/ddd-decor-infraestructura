<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 5/30/2019
 * Time: 4:40 PM
 */

namespace App\Http\Controllers\Api\Ticket;


use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketType;
use Illuminate\Http\Response;

class TicketTypeController extends Controller
{

    public function index()
    {
        try {
            $data = TicketType::with('sub_types')
                ->select('id', 'name')
                ->orderBy('id', 'asc')
                ->get();


            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_TYPE,
                    'types' => $data
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

}