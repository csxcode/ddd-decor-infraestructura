<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonHelper;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\User;
use App\UseCases\Ticket\PhotoPostProcess;
use App\Validations\TicketPhotoValidation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TicketPhotoController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET_PHOTO;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store($id, TicketPhotoValidation $ticketPhotoValidation, PhotoPostProcess $postProcessAction)
    {
        // Check if json is valid
        if (!$this->request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::where('id', $id)->first();
            $photosData = [];

            // Validations            
            $error_response = $ticketPhotoValidation->store($this->request, $this->resource, $user, $ticket, $photosData);

            if ($error_response)
                return $error_response;

            // Save            
            DB::beginTransaction();
            $postProcessAction->execute($photosData, $id);
            DB::commit();

            // Return Data            
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_PHOTO,
                    'ticket_id' => $ticket->id
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
