<?php namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\FileHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\Base64Helper;
use App\Helpers\JsonHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Ticket\Validations\TicketControllerValidation;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketComment;
use App\Models\Ticket\TicketPhoto;
use App\Models\Ticket\TicketStatus;
use App\Models\User;
use App\Models\Views\TicketSearch;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TicketController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function Create()
    {
        // Check if json is valid
        if(!$this->request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $save = [];

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketControllerValidation::CreateValidation($this->request, $this->resource, $user, $save);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            // Add Ticket
            $ticket = Ticket::create([
                'ticket_number' => Ticket::GenerateNumber(),
                'status_id' => $save['status_id'],
                'priority_id' => $save['priority_id'],
                'type_id' => $save['type_id'],
                'branch_id' => $save['branch_id'],
                'description' => $save['description'],
                'status_reason' => $save['status_reason'],
                'delivery_date' => $save['delivery_date'],
                'subtype_id' => $save['subtype_id'],
                'location' => $save['location'],
                'created_at' => Carbon::now(),
                'created_by_user' => User::GetCreatedByUser($user)
            ]);

            DB::commit();


            // --------------------------------------------
            // Send Emails
            // --------------------------------------------
            try{
               Ticket::SendEmailNewTicket($ticket->id, $save['branch_id'], $user);
            } catch (\Exception $e) {
                ErrorHelper::SendInternalErrorMessageForApi($e);
            }


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET,
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
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

            $columns = '
                ticket.id,
                ticket.ticket_number,
                ticket.status_id,
                ticket_status.name as status_name,
                ticket.type_id,
                ticket_type.name as type_name,
                branch.branch_id,
                branch.name as branch_name,
                store.store_id,
                store.name as store_name,
                ticket.description,
                ticket.status_reason,
                ticket.delivery_date,
                ticket_type_sub.id as subtype_id,
                ticket_type_sub.name as subtype_name,
                ticket.created_by_user,
                ticket.created_at,
                ticket.updated_by_user,
                ticket.updated_at,
                ticket.approved_by_user,
                ticket.approved_at,
                ticket.rejected_by_user,
                ticket.rejected_at,
                ticket.priority_id,
                priority.name as priority_name,
                ticket.video_guid,
                ticket.video_name,
                ticket.location,
                ticket.resolution_comment
            ';

            $columns = ltrim(rtrim($columns));

            $ticket = Ticket::select(DB::raw($columns))
                ->leftJoin('branch', 'ticket.branch_id', 'branch.branch_id')
                ->leftJoin('store', 'branch.store_id', 'store.store_id')
                ->leftJoin('ticket_status', 'ticket.status_id', 'ticket_status.id')
                ->leftJoin('ticket_type', 'ticket.type_id', 'ticket_type.id')
                ->leftJoin('ticket_type_sub', 'ticket.subtype_id', 'ticket_type_sub.id')
                ->leftJoin('priority', 'ticket.priority_id', 'priority.id')
                ->where('ticket.id', $id)
                ->first();

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketControllerValidation::GetValidation($this->request, $this->resource, $user, $ticket);

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $photos = TicketPhoto::select('id', 'order', 'guid', 'name', 'ticket_id', 'type')
                ->where('ticket_id', $ticket->id)
                ->orderBy('type')
                ->orderBy('order')
                ->get();

            $photos->makeHidden('ticket_id');
            $photos->makeHidden('id');

            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET,
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'status_id' => $ticket->status_id,
                    'status_name' => $ticket->status_name,
                    'status_reason' => $ticket->status_reason,
                    'type_id' => $ticket->type_id,
                    'type_name' => $ticket->type_name,
                    'branch_id' => $ticket->branch_id,
                    'branch_name' => $ticket->branch_name,
                    'store_id' => $ticket->store_id,
                    'store_name' => $ticket->store_name,
                    'description' => $ticket->description,
                    'photos' => $photos,
                    'video_name' => $ticket->video_name,
                    'video_guid' => $ticket->video_guid,
                    'delivery_date' => ($ticket->delivery_date == null ? null : Carbon::parse($ticket->delivery_date)->timestamp),
                    'subtype_id' => $ticket->subtype_id,
                    'subtype_name' => $ticket->subtype_name,
                    'priority_id' => $ticket->priority_id,
                    'priority_name' => $ticket->priority_name,
                    'location' => $ticket->location,
                    'resolution_comment' => $ticket->resolution_comment,
                    'created_by_user' => $ticket->created_by_user,
                    'created_at' => Carbon::parse($ticket->created_at)->timestamp,
                    'updated_by_user' => $ticket->updated_by_user,
                    'updated_at' => ($ticket->updated_at == null ? null : Carbon::parse($ticket->updated_at)->timestamp),
                    'approved_by_user' => $ticket->approved_by_user,
                    'approved_at' => ($ticket->approved_at == null ? null : Carbon::parse($ticket->approved_at)->timestamp),
                    'rejected_by_user' => $ticket->rejected_by_user,
                    'rejected_at' => ($ticket->rejected_at == null ? null : Carbon::parse($ticket->rejected_at)->timestamp),
                ],
                Response::HTTP_OK
            );


        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function All()
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
            $error_response = TicketControllerValidation::AllValidation($this->request, $this->resource);

            if($error_response)
                return $error_response;

            // Set values by default
            PaginateHelper::SetPaginateDefaultValues($page, $per_page);

            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $data = Ticket::Search(
                AccessTypeEnum::Api,
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

    public function Update($id)
    {
        // Check if json is valid
        if (!$this->request->json()->all()) {
            return JsonHelper::ReturnResponseJsonInValid();
        }

        try {

            $JsonData = $this->request->all();
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);
            $ticket_clone = TicketSearch::where('id', $id)->first();
            $changes = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketControllerValidation::UpdateValidation(
                $this->request, $this->resource, $user,
                $ticket, $changes);

            if ($error_response)
                return $error_response;


            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            // update only if there are changes
            if ($changes) {

                // ticket
                $ticket->updated_by_user = User::GetCreatedByUser($user);
                $ticket->updated_at = Carbon::now();
                $ticket->save();

                // ticket_comment
                // Llevar un histórico del cambio de estado del ticket
                if(array_key_exists('status_id', $changes)){

                    $ticket_status_changed = TicketStatus::find($changes['status_id']);
                    $ticket_comment_description_line_1 = 'Estado del ticket cambió de ' . $ticket_clone->status_name . ' a ' . $ticket_status_changed->name;
                    $ticket_comment_description_line_2 = null;

                    if(array_key_exists('log_status_reason', $changes)){

                        if($changes['log_status_reason'] != null){
                            $ticket_comment_description_line_2 = "\r\nMotivo: " . $changes['log_status_reason'];
                        }

                    }

                    $ticket_comment_description = $ticket_comment_description_line_1 . $ticket_comment_description_line_2;

                    TicketComment::create([
                        'ticket_id' => $id,
                        'description' => $ticket_comment_description,
                        'created_at' => Carbon::now(),
                        'created_by_user' => User::GetCreatedByUser($user)
                    ]);

                }

            }

            DB::commit();

            // --------------------------------------------
            // Return Data
            // --------------------------------------------

            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET,
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'updated_at' => ($ticket->updated_at == null ? null : Carbon::parse($ticket->updated_at)->timestamp)
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function VideoProcess($id)
    {

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::find($id);
            $ticket_clone = clone $ticket;
            $data = [];

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketControllerValidation::VideoProcessValidation($this->request, $this->resource, $user, $ticket, $data);

            if($error_response)
                return $error_response;

            // Check if the folder is already created otherwise will be create
            $folder_path = Config::get('app.path_ticket_photos') . $ticket->id . '/';
            if (!file_exists($folder_path)) {
                mkdir($folder_path, 0777, true);
            }

            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            $files_to_delete = [];
            $message = null;

            foreach($data as $item) {

                if($item['action'] == ActionEnum::DELETE) {

                    // update data
                    $ticket->update(
                        [
                            'video_guid' => null,
                            'video_name' => null
                        ]
                    );

                    // add to global array to delete
                    $path_file_to_delete = $folder_path . $ticket['video_guid'] . '.' . FileHelper::GetExtensionFromFilename($ticket_clone['video_name']);
                    array_push($files_to_delete, $path_file_to_delete);

                    $message = 'video was delete successfully';

                }else {

                    // update data
                    $guid = FunctionHelper::CreateGUID(16);

                    $ticket->update(
                        [
                            'video_guid' => $guid,
                            'video_name' => $item['video']->getClientOriginalName()
                        ]
                    );

                    // save video to disk
                    $extension = $item['video']->getClientOriginalExtension();
                    $safeName = $guid . '.' . $extension;

                    $item['video']->move($folder_path, $safeName);

                    // add to global array to delete
                    if($item['action'] == ActionEnum::EDIT){
                        $path_file_to_delete = $folder_path . $ticket_clone['video_guid'] . '.' . FileHelper::GetExtensionFromFilename($ticket_clone['video_name']);
                        array_push($files_to_delete, $path_file_to_delete);

                        $message = 'video was updated successfully';
                    }else{
                        $message = 'video was added successfully';
                    }

                }

            }

            DB::commit();

            // delete massive files
            try{

                foreach ($files_to_delete as $path){
                    if(file_exists($path)){
                        File::delete($path);
                    }
                }
            } catch (\Exception $e) {
                MailService::SendErrorMail($e, AccessTypeEnum::Api);
            }

            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET,
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
