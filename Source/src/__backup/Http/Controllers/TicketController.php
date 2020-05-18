<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Ticket\Validations\TicketControllerValidation;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Branch;
use App\Models\Store;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketPhoto;
use App\Models\Ticket\TicketStatus;
use App\Models\Ticket\TicketType;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Models\Views\TicketComponents;
use App\Models\Views\TicketSearch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as ResponseFacades;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $ticket_number = $this->request->get('ticket_number');
        $store_id = $this->request->get('store');
        $branch_id = $this->request->get('branch');
        $type_id = $this->request->get('type');
        $date_from = $this->request->get('date_from');
        $date_to = $this->request->get('date_to');
        $status_id = $this->request->get('status');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        $usb_allowed = null;
        $user = Auth::user();

        if(GlobalValidation::UserNeedToFilterData($user)){
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
        }

        //get data
        $data = Ticket::Search(
            AccessTypeEnum::Web,
            compact('usb_allowed', 'ticket_number', 'store_id', 'branch_id', 'type_id', 'date_from', 'date_to', 'status_id'),
            compact('sort', 'direction')
        );

        $stores = Store::GetUserStores($user->user_id)->pluck('store_name', 'store_id');
        $stores->prepend(trans('global.all_select'), '');

        $branches = Branch::GetUserBranches($user->user_id, $store_id)->pluck('branch_name', 'branch_id');
        $branches->prepend(trans('global.all_select'), '');

        $types = TicketType::orderBy('name')->pluck('name', 'id');
        $types->prepend(trans('global.all_select'), '');

        $status = TicketStatus::orderBy('id')->pluck('name', 'id');
        $status->prepend(trans('global.all_select'), '');

        return view('tickets.index')->with(compact('data', 'stores', 'branches', 'types', 'status'));
    }

    public function show($id)
    {
        $action = ActionEnum::VIEW;
        $user = Auth::user();

        // Get Data
        $ticket = TicketSearch::where('ticket_number', $id)->first();

        // Check data if exists
        if (!$ticket) {
            return view('errors.404');
        }

        // Validations
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $ticket->branch_id, null, null, null);

        if($error_response)
            return view('errors.403');

        // Get and Set Data
        $status = null;
        $types = null;
        $exhibitions = null;
        $components = null;
        $photos = null;
        self::GetAndSetData($ticket, $status, $types, $exhibitions, $components, $photos);

        // Return Data
        return view('tickets.show')->with(compact(
            'action',
            'ticket',
            'status',
            'types',
            'exhibitions',
            'components',
            'photos'
        ));
    }

    public function edit($id)
    {
        $action = ActionEnum::EDIT;
        $user = Auth::user();

        // Get Data
        $ticket = TicketSearch::where('ticket_number', $id)->first();

        // Check data if exists
        if (!$ticket) {
            return view('errors.404');
        }

        // Validations
        if (!self::CheckIfUserCanEdit($ticket->status_id))
            return view('errors.403');

        // Validations
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $ticket->branch_id, null, null, null);

        if($error_response)
            return view('errors.403');


        // Get and Set Data
        $status = null;
        $types = null;
        $exhibitions = null;
        $components = null;
        $photos = null;
        self::GetAndSetData($ticket, $status, $types, $exhibitions, $components, $photos);

        // Return Data
        return view('tickets.show')->with(compact(
            'action',
            'ticket',
            'status',
            'types',
            'exhibitions',
            'components',
            'photos'
        ));
    }

    public function Export()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $ticket_number = $this->request->get('ticket_number');
        $store_id = $this->request->get('store');
        $branch_id = $this->request->get('branch');
        $type_id = $this->request->get('type');
        $date_from = $this->request->get('date_from');
        $date_to = $this->request->get('date_to');
        $status_id = $this->request->get('status');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        $usb_allowed = null;
        $user = Auth::user();

        if(GlobalValidation::UserNeedToFilterData($user)){
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
        }

        //get data
        $data = Ticket::Export(
            compact('usb_allowed', 'ticket_number', 'store_id', 'branch_id', 'type_id', 'date_from', 'date_to', 'status_id'),
            compact('sort', 'direction')
        );

        return Excel::create('tickets', function($excel) use ($data) {

            $excel->sheet('Tickets', function($sheet) use ($data) {
                $sheet->setHeight(array(
                    1     =>  16,
                    2     =>  16
                ));

                $sheet->setFontSize(10);

                $sheet->loadView('templates.excel.tickets')->with(['data' => $data->toArray()]);
                $sheet->setFreeze("A3");
                $sheet->setAutoFilter('A2:X2');

                $sheet->cells('A1:X2', function($cells) {
                    $cells->setAlignment('center');
                });
            });

        })->export('xlsx');

    }

    public static function CheckIfUserCanEdit($status_id){

        $canEdit = true;
        $user = Auth::user();

        if (!$user->hasRole('supervisor', 'tr', 'admin')){
            $canEdit = false;
        }

        if($status_id != TicketStatus::TICKET_STATUS_NEW && $status_id != TicketStatus::TICKET_STATUS_PROCESS){
            $canEdit = false;
        }

        return $canEdit;
    }

    public static function GetActionIconRelated($action_id){
        $return = null;

        if($action_id == 1){
            $return = 'check.png';
        } else if($action_id == 2) {
            $return = 'gear.png';
        } else if($action_id == 3) {
            $return = 'tools.png';
        } else if($action_id == 4) {
            $return = 'package.png';
        } else if($action_id == 5) {
            $return = 'sign-in.png';
        }

        $return = '/images/icons/' . $return;
        return asset($return);
    }

    private static function GetAndSetData($ticket, &$status, &$types, &$exhibitions, &$components, &$photos){

        $user = Auth::user();

        // Get Data
        $status = TicketStatus::orderBy('id')->pluck('name', 'id');
        $status->prepend('', '');

        $types = TicketType::orderBy('name')->pluck('name', 'id');
        $types->prepend('', '');

        // Get Components
        $components = TicketComponents::where('ticket_id', $ticket->id)
            ->orderBy('type_id', 'asc')
            ->orderBy('name', 'asc')
            ->orderBy('action_name', 'asc')->get();

        $photos = TicketPhoto::where('ticket_id', $ticket->id)
            ->orderBy('order')->get();
    }

    public function GetPhoto($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $ticket = Ticket::find($id);

            // [Ticket]
            if (!$ticket) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Ticket']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $ticket_photo = TicketPhoto::where('ticket_id', $ticket->id)
                ->where('guid', $guid)
                ->first();

            if (!$ticket_photo) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid de la foto del ticket']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'guid'
                    ]
                ], Response::HTTP_BAD_REQUEST);

            }


            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($ticket_photo->name);
            $path = Config::get('app.path_ticket_photos') . $ticket->id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $ticket_photo->name . '"');

                $return = $response;
            }else{
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Imagen']),
                    'error_code' => ErrorCodesEnum::not_exists
                ], Response::HTTP_BAD_REQUEST);
            }

            return $return;

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

    }

    public function GetVideo($id, $guid)
    {
        try {

            $return = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $ticket = Ticket::find($id);

            // [Ticket]
            if (!$ticket) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Ticket']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            foreach(TicketControllerValidation::$video_ext as $ext){
                $_ext = '.' . $ext;
                $guid = str_replace($_ext, '', $guid);
            }

            $guid = StringHelper::Trim($guid);

            if ($ticket->video_guid != $guid) {

                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid de video del ticket']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'guid'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $video_name = $ticket->video_name;

            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($video_name);
            $path = Config::get('app.path_ticket_photos') . $ticket->id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','inline; filename="' . $video_name . '"');

                $return = $response;
            }else{
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Video']),
                    'error_code' => ErrorCodesEnum::not_exists
                ], Response::HTTP_BAD_REQUEST);
            }

            return $return;

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

    }


    /* --------------------------------------------- */
    /* AJAX METHODS */
    /* --------------------------------------------- */
    public static function DownloadPhoto($id, $guid)
    {
        try{
            $filename = null;

            $ticket = Ticket::find($id);

            // [Checklist]
            if (!$ticket) {
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Ticket']),
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $data = TicketPhoto::where('ticket_id', $ticket->id)
                ->where('guid', $guid)
                ->first();

            if (!$data) {
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid de la foto del checklist']),
                ], Response::HTTP_BAD_REQUEST);
            }

            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($data->name);

            $path = Config::get('app.path_ticket_photos') . $id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','attachment; filename="' . $data->name . '"');

                $return = $response;
            }else{
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Imagen']),
                ], Response::HTTP_BAD_REQUEST);
            }

            return $return;

        }catch(\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al descargar la imagen'
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function SaveData($id)
    {
        $html_data_tab = null;
        $html_history_tab = null;
        $data = Ticket::find($id);

        // Validations
        if(!$data)
            return response()->json(['errors' => ['Ticket no existe']], Response::HTTP_BAD_REQUEST);

        if (!self::CheckIfUserCanEdit($data->status_id))
            return response()->json(['errors' => ['Este ticket no se puede editar']], Response::HTTP_BAD_REQUEST);

        if($data->type_id == TicketType::TICKET_TYPE_NUEVA_EXHIBICION && $this->request->get('status') == TicketStatus::TICKET_STATUS_CLOSED)
            return response()->json(['errors' => ['Este ticket debe ser cerrado desde el app Android, luego de haber sido registrado la nueva exhibición.']], Response::HTTP_BAD_REQUEST);

        if($this->request->get('status') == TicketStatus::TICKET_STATUS_CANCELED){
            if($this->request->get('__reason') == null || $this->request->get('__reason') == ''){
                return response()->json(['errors' => ['El motivo es requerido cuando se selecciona un estado de tipo cancelado']], Response::HTTP_BAD_REQUEST);
            }
        }

        // Save Data
        $user = Auth::user();

        $data->status_id = $this->request->get('status');
        $data->description = $this->request->get('description');

        $del_date = null;
        try{
            $del_date =Carbon::createFromFormat('d/m/Y', $this->request->get('delivery_date'));
        }catch (\Exception $ex){
        }

        $data->delivery_date = $del_date;

        if($this->request->get('status') == TicketStatus::TICKET_STATUS_PROCESS){

            $data->approved_at = Carbon::now();
            $data->approved_by_user = User::GetCreatedByUser($user);

        } else if($this->request->get('status') == TicketStatus::TICKET_STATUS_CANCELED){

            $data->status_reason = $this->request->get('__reason');
            $data->rejected_at = Carbon::now();
            $data->rejected_by_user = User::GetCreatedByUser($user);

        }

        $data->updated_at = Carbon::now();
        $data->updated_by_user = User::GetCreatedByUser($user);

        $data->save();

        // Return
        return response()->json([], Response::HTTP_OK);
    }

}
