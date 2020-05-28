<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonHelper;
use App\Transformers\TicketCommentTransformer;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\User;
use App\UseCases\TicketComment\Search;
use App\UseCases\TicketComment\Create;
use App\Validations\TicketCommentValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TicketCommentController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET_COMMENT;
    protected $request;
    private $ticketCommentValidation;

    public function __construct(Request $request, TicketCommentValidation $ticketCommentValidation)
    {
        $this->request = $request;
        $this->ticketCommentValidation = $ticketCommentValidation;
    }

    public function store($id, Create $createAction)
    {
        // Check if json is valid
        if (!$this->request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);
            $save = [];

            // Validations            
            $error_response = $this->ticketCommentValidation->store($this->request, $this->resource, $user, $ticket, $save);

            if ($error_response)
                return $error_response;

            // Save            
            DB::beginTransaction();
            $ticketComment = $createAction->execute($ticket->id, $save, $user);
            DB::commit();

            // Return Data            
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_COMMENT,
                    'id' => $ticketComment->id,
                    'created_at' => Carbon::parse($ticket->created_at)->timestamp
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function index($id, Search $searchAction)
    {
        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);

            // Validations
            $error_response = $error_response = $this->ticketCommentValidation->index($this->resource, $user, $ticket);

            if ($error_response)
                return $error_response;

            // Get Data
            $data = $searchAction->execute($ticket->id);

            // Return Data
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_COMMENT,
                    'comments' => (new TicketCommentTransformer)->show($data)
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
