<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Transformers\TicketTransformer;
use App\UseCases\Ticket\Create;
use App\UseCases\Ticket\Show;
use App\UseCases\Ticket\Update;
use App\UseCases\Ticket\Search;
use App\Validations\TicketValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET;
    protected $request;
    private $ticketValidation;

    public function __construct(Request $request, TicketValidation $ticketValidation)
    {
        $this->request = $request;
        $this->ticketValidation = $ticketValidation;
    }

    public function store()
    {
        // Check if json is valid
        if (!$this->request->json()->all()) {
            return JsonHelper::ReturnResponseJsonInValid();
        }

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $save = [];

            // Validations        
            $error_response = $this->ticketValidation->store($this->request, $this->resource, $user, $save);

            if ($error_response) {
                return $error_response;
            }

            // Save            
            DB::beginTransaction();
            $ticket = (new Create())->execute($save, $user);
            DB::commit();

            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET,
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'created_at' => Carbon::parse($ticket->created_at)->timestamp,
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function show($id, Show $showAction)
    {
        try {
            $user = User::GetByToken($this->request->bearerToken());

            $error_response = $this->ticketValidation->show($this->resource, $user, $id);

            if ($error_response) {
                return $error_response;
            }

            return response()->json(
                (new TicketTransformer())->show(AppObjectNameEnum::TICKET, $showAction->execute($id)),
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function index(Search $searchAction)
    {
        try {
            $user = User::GetByToken($this->request->bearerToken());

            // --------------------------------------------
            // Get Data From Request
            // --------------------------------------------
            $store_id = StringHelper::Trim($this->request->get('store_id'));
            $branch_id = StringHelper::Trim($this->request->get('branch_id'));
            $status_id = StringHelper::Trim($this->request->get('status_id'));
            $type_id = StringHelper::Trim($this->request->get('type_id'));
            $date_from = StringHelper::Trim($this->request->get('date_from'));
            $date_to = StringHelper::Trim($this->request->get('date_to'));
            $per_page = StringHelper::Trim($this->request->get('per_page'));
            $page = StringHelper::Trim($this->request->get('page'));

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->ticketValidation->index($this->request, $this->resource);

            if ($error_response) {
                return $error_response;
            }

            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = $searchAction->execute(
                compact('user', 'store_id', 'branch_id', 'status_id', 'type_id', 'date_from', 'date_to', 'per_page', 'page'),
                ['sort' => 'ticket_number', 'direction' => 'desc']
            );

            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                PaginateHelper::TransformPaginateData($this->resource, 'tickets', $data),
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function update($id)
    {
        // Check if json is valid
        if (!$this->request->json()->all()) {
            return JsonHelper::ReturnResponseJsonInValid();
        }

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);
            $changes = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = $this->ticketValidation->update(
                $this->request,
                $this->resource,
                $user,
                $ticket,
                $changes
            );

            if ($error_response) {
                return $error_response;
            }
            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();
            $ticket = (new Update())->execute($changes, $ticket, $user);
            DB::commit();

            // --------------------------------------------
            // Return Data
            // --------------------------------------------

            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET,
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'updated_at' => (null == $ticket->updated_at ? null : Carbon::parse($ticket->updated_at)->timestamp),
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
}
