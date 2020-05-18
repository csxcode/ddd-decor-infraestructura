<?php

namespace App\Models\Ticket;

use App\Enums\AccessTypeEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\FileHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\Base64Helper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Models\Views\TicketSearch;
use App\Models\Views\vEquivalencesChecklistTicketType;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    protected $table = 'ticket';
    protected $guarded = ['id'];

    public static function GenerateNumber(){
        $ini_counter = 1000;
        $max = Ticket::max('ticket_number');
        if(!$max)
            $max = $ini_counter;
        $max = ((int)$max + 1);
        return $max;
    }


    public static function Search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;

        if($accessType == AccessTypeEnum::Api) {
            $columns = '
                id,
                ticket_number,
                status_id,
                status_name,
                type_id,
                type_name,
                store_id,
                store_name,
                branch_id,
                branch_name,
                subtype_id,
                subtype_name,
                priority_id,
                priority_name,
                checklist_id,
                UNIX_TIMESTAMP(CONVERT_TZ(created_at, \'+00:00\', @@global.time_zone)) created_at
            ';
        }else if($accessType == AccessTypeEnum::Web) {
            $columns = '
                id,
                ticket_number,
                status_name,
                type_name,
                store_name,
                branch_name,
                status_id,
                created_at
            ';
        }

        $columns = ltrim(rtrim($columns));

        $query = TicketSearch::select(DB::raw($columns));

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        self::SetFilterForSearchAndExport($filterParams, $accessType, $query);

        // -------------------------------------
        // Set Paginate
        // -------------------------------------
        $per_page = null;

        if ($accessType == AccessTypeEnum::Api) {
            $page = $filterParams['page'];
            $per_page = $filterParams['per_page'];

            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        } else {
            $per_page = Config::get('app.paginate_default_per_page');
        }

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = 'ticket_number'; //use default sort

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];
        }

        $query->orderBy($sort, $direction);

        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->paginate($per_page);
    }

    public static function Export($filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = '*, TicketSearch.type_name as ticket_type_name';

        $columns = ltrim(rtrim($columns));

        $query = TicketSearch::select(DB::raw($columns));
        $query->leftJoin('TicketComponents', 'TicketSearch.id', DB::raw('TicketComponents.ticket_id and ifnull(TicketComponents.action_id, 0) <> 1'));

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        self::SetFilterForSearchAndExport($filterParams, AccessTypeEnum::Web, $query);
        $query->whereRaw('ifnull(TicketComponents.action_id, 0) <> 1'); //<> Mantener componente

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = 'TicketSearch.ticket_number'; //use default sort

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = 'TicketSearch.'.$sortByParams['sort'];
        }

        $query->orderBy($sort, $direction)
            ->orderBy('TicketComponents.type_id', 'asc')
            ->orderBy('TicketComponents.name', 'asc')
            ->orderBy('TicketComponents.action_name', 'asc');

        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->get();
    }

    private static function SetFilterForSearchAndExport($filterParams, $accessType, &$query){
        if (isset($filterParams['ticket_number']) && !StringHelper::IsNullOrEmptyString($filterParams['ticket_number'])) {
            $query->where('ticket_number', $filterParams['ticket_number']);
        }

        if (isset($filterParams['store_id']) && !StringHelper::IsNullOrEmptyString($filterParams['store_id'])) {
            $query->where('store_id', $filterParams['store_id']);
        }

        if (isset($filterParams['branch_id']) && !StringHelper::IsNullOrEmptyString($filterParams['branch_id'])) {
            $query->where('branch_id', $filterParams['branch_id']);
        }

        if (isset($filterParams['status_id']) && !StringHelper::IsNullOrEmptyString($filterParams['status_id'])) {
            $query->where('status_id', $filterParams['status_id']);
        }

        if (isset($filterParams['type_id']) && !StringHelper::IsNullOrEmptyString($filterParams['type_id'])) {
            $query->where('type_id', $filterParams['type_id']);
        }

        if($accessType == AccessTypeEnum::Api) {

            if (isset($filterParams['date_from']) && !StringHelper::IsNullOrEmptyString($filterParams['date_from'])) {
                $date_from = Carbon::createFromTimestamp($filterParams['date_from'])->toDateString();
                $date_from = date('Y-m-d 00:00:00', strtotime($date_from));
                $query->where('created_at', '>=', $date_from);
            }

            if (isset($filterParams['date_to']) && !StringHelper::IsNullOrEmptyString($filterParams['date_to'])) {
                $date_to = Carbon::createFromTimestamp($filterParams['date_to'])->toDateString();
                $date_to = date('Y-m-d 23:59:59', strtotime($date_to));
                $query->where('created_at', '<=', $date_to);
            }

        } else {

            if (isset($filterParams['date_from']) and trim($filterParams['date_from']) != '') {
                $date_from = Carbon::createFromFormat('d/m/Y', $filterParams['date_from'])->toDateString();
                $date_from = date('Y-m-d 00:00:00', strtotime($date_from));
                $w_raw = sprintf("CONVERT_TZ(created_at, '+00:00', '%s') >= '%s'", Config::get('app.utc_offset'), $date_from);
                $query->whereRaw($w_raw);
            }

            if (isset($filterParams['date_to']) and trim($filterParams['date_to']) != '') {
                $date_to = Carbon::createFromFormat('d/m/Y', $filterParams['date_to'])->toDateString();
                $date_to = date('Y-m-d 23:59:59', strtotime($date_to));
                $w_raw = sprintf("CONVERT_TZ(created_at, '+00:00', '%s') <= '%s'", Config::get('app.utc_offset'), $date_to);
                $query->whereRaw($w_raw);
            }

        }

        // ------------------- [INI] Rules for USER ---------------------------
        if (isset($filterParams['user']) && !StringHelper::IsNullOrEmptyString($filterParams['user'])) {

            $user = $filterParams['user'];
            $role = strtolower($user->role->name);

            if ($role == strtolower(UserRoleEnum::ADMIN) || $role == strtolower(UserRoleEnum::VISUAL)) {

                // Visual Usuario (2) or admin, cualquier ticket de cualquier sucursal
                $all = true;

            } else if ($role == strtolower(UserRoleEnum::STORE_MANAGER)) {

                // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
                $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
                $query->whereIn('branch_id', $usb_allowed);

            } else {

                // Cualquier otro tipo de usuario, solo si el ticket pertenece a la
                // sucursal que tiene asignado el usuario y el ticket haya sido creado por el usuario
                $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
                $username_from_user = strtolower($user->username);

                $query->whereIn('branch_id', $usb_allowed);
                $query->where('ticket_created_by_username', $username_from_user);

            }

        }
        // ------------------- [END] Rules for USER ---------------------------

    }


    public static function SendEmailNewTicket($ticket_id, $branch_id, $user){

        $emails_all_users_visual_type = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::VISUAL_ID);
        $emails_all_users_store_manager_type_related = UserStoreBranch::GetUsersEmailByRoleAndBranchRelated(UserRoleEnum::STORE_MANAGER_ID, $branch_id);

        $emails = array_merge($emails_all_users_visual_type, $emails_all_users_store_manager_type_related);

        if(count($emails)>0){

            $to = Array();

            // get emails (to)
            foreach ($emails as $item){
                array_push($to, $item->email);
            }

            // get data
            $cc = $user->email;
            $bcc = null;
            $data = TicketSearch::where('id', $ticket_id)->first();
            $subject = str_replace(':number', $data->ticket_number, Config::get('app.mail_new_ticket_subject'));

            // send email
            MailService::SendMainMail(
                'templates.emails.ticket.new',
                compact('data'),
                Config::get('app.mail_new_ticket_from'),
                Config::get('app.mail_new_ticket_from_head'),
                $to,
                $cc,
                $bcc,
                $subject
            );

        }
    }

    public static function CreateTicketsFromChecklist($checklist, $user){

        // $user = the user that created the checklist otherwise if it does not exists then the current user

        $items_for_generate_ticket = DB::table('checklist_item_details')->where('checklist_id', $checklist->id)->where('disagreement_generate_ticket', 1)->get();

        if(count($items_for_generate_ticket) > 0){

            $checklist_item_ids = $items_for_generate_ticket->pluck('checklist_item_id');
            $equivalences = vEquivalencesChecklistTicketType::GetEquivalences($checklist_item_ids);

            if(count($equivalences) > 0){

                $exhibition = DB::table('exhibition')->where('exhibition_id', $checklist->exhibition_id)->first();

                foreach ($equivalences as $checklist_type_id => $checklist_types){

                    foreach ($checklist_types as $ticket_type_id => $checklist_items){

                        $description = null;
                        $photos = Array();

                        // Ini: Build Description and Get/Set Photos
                        foreach ($checklist_items as $item) {

                            $checklist_item_detail = $items_for_generate_ticket->filter(function ($data) use ($item) {
                                return $data->checklist_item_id == $item->checklist_item_id;
                            })->first();


                            $description .= ($description == null ? "" : "\n\n") .
                                $item->checklist_type_name . ": " . $item->checklist_item_name . "\n" .
                                $checklist_item_detail->disagreement_reason;

                            // check if there is photo
                            if($checklist_item_detail->disagreement_photo_guid){
                                $add_photo = [
                                  'guid_ticket' => FunctionHelper::CreateGUID(16),
                                  'guid_checklist' => $checklist_item_detail->disagreement_photo_guid,
                                  'name' => $checklist_item_detail->disagreement_photo_name,
                                  'extension' => FileHelper::GetExtensionFromFilename($checklist_item_detail->disagreement_photo_name)
                                ];

                                array_push($photos, $add_photo);
                            }
                        }
                        // End: Build Description and Get/Set Photos


                        // Add Ticket
                        $ticket = Ticket::create([
                            'ticket_number' => Ticket::GenerateNumber(),
                            'status_id' => TicketStatus::TICKET_STATUS_NEW,
                            'type_id' => $ticket_type_id,
                            'exhibition_id' => $checklist->exhibition_id,
                            'branch_id' => $exhibition->branch_id,
                            'description' => $description,
                            'subtype_id' => $item->ticket_type_sub_id,
                            'created_at' => Carbon::now(),
                            'created_by_user' => User::GetCreatedByUser($user),
                            'updated_at' => null
                        ]);

                        // Add Photos
                        if(count($photos)>0){

                            $folder_path_checklist = Config::get('app.path_checklist_photos') . $checklist->id . '/';

                            // Check if the folder is already created otherwise will be create
                            $folder_path_ticket = Config::get('app.path_ticket_photos') . $ticket->id . '/';
                            if (!file_exists($folder_path_ticket)) {
                                mkdir($folder_path_ticket, 0777, true);
                            }

                            $counter_photo = 0;

                            foreach ($photos as $photo){

                                $counter_photo += 1;

                                TicketPhoto::create([
                                    'ticket_id' => $ticket->id,
                                    'guid' => $photo['guid_ticket'],
                                    'name' => $photo['name'],
                                    'order' => $counter_photo
                                ]);

                                // Copy image from checklist to ticket folder
                                $path_photo_checklist = $folder_path_checklist . $photo['guid_checklist'] . '.' . $photo['extension'];
                                $path_photo_ticket = $folder_path_ticket . $photo['guid_ticket'] . '.' . $photo['extension'];

                                if(!copy($path_photo_checklist , $path_photo_ticket)){
                                    $error = 'Image can not copy, $path_photo_checklist: [' . $path_photo_checklist . '], $path_photo_ticket: [' . $path_photo_ticket . ']';
                                    throw new \Exception($error);
                                }

                            }

                        }


                        // Send Emails
                        try{
                            Ticket::SendEmailNewTicket($ticket->id, $ticket->branch_id, $user);
                        } catch (\Exception $e) {
                            ErrorHelper::SendInternalErrorMessageForApi($e);
                        }


                    }

                }

            }

        }

    }

    public static function GetImageURL($ticket_id, $photo_guid){
        $url = Config::get('app.web_ticket_photo_path');
        $url = str_replace('{id}', $ticket_id, $url);
        $url = str_replace('{guid}', $photo_guid, $url);

        if($photo_guid == null){
            $url = Config::get('app.web_url') . '/images/no-image.png';
        }

        return $url;
    }

    private static function columnsAllowedForSortBy(){
        return [
            'id',
            'ticket_number',
            'created_at',
            'type_name',
            'store_name',
            'status_name'
        ];
    }

}
