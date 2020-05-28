<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\User;
use App\UseCases\Ticket\VideoPostProcess;
use App\Validations\TicketVideoValidation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TicketVideoController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store($id, TicketVideoValidation $ticketVideoValidation, VideoPostProcess $videoPostProcessAction)
    {
        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);
            $data = [];

            // Validations            
            $error_response = $ticketVideoValidation->store($this->request, $this->resource, $user, $ticket, $data);

            if ($error_response)
                return $error_response;

            // Save            
            DB::beginTransaction();
            $message = $videoPostProcessAction->execute($data, $ticket->id);
            DB::commit();

            // Return Data            
            return response()->json(
                [
                    'object' => $this->resource,
                    'id' => $ticket->id,
                    'response' => [
                        'message' => $message
                    ]
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
