<?php namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonHelper;
use App\Transformers\TicketCommentTransformer;
use App\Http\Controllers\Api\Ticket\Validations\TicketCommentControllerValidation;
use App\Http\Controllers\Api\Ticket\Validations\TicketControllerValidation;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketComment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TicketCommentController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET_COMMENT;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function Create($id)
    {
        // Check if json is valid
        if(!$this->request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();

        try {                
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);
            $save = [];

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketCommentControllerValidation::CreateValidation($this->request, $this->resource, $user, $ticket, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            // Add Ticket
            $ticket_comment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'description' => $save['description'],               
                'created_at' => Carbon::now(),
                'created_by_user' => User::GetCreatedByUser($user)                              
            ]);         

            DB::commit();            


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_COMMENT,
                    'id' => $ticket_comment->id,                    
                    'created_at' => Carbon::parse($ticket->created_at)->timestamp
                ],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function Get($id)
    {
        try {
            $user = User::GetByToken($this->request->bearerToken());
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);           

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketControllerValidation::GetValidation($this->request, $this->resource, $user, $ticket);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------           
            $data = TicketComment::select(DB::raw(                
                'id, 
                description, 
                UNIX_TIMESTAMP(CONVERT_TZ(created_at, \'+00:00\', @@global.time_zone)) as created_atx,
                created_by_user'
            ))
            ->where('ticket_id', $ticket->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();  

            $data_to_return = (new TicketCommentTransformer)->show($data);            


            // --------------------------------------------
            // Return Data
            // --------------------------------------------

            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_COMMENT,
                    'comments' => $data_to_return
                ],
                Response::HTTP_OK
            );           

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }
   
}