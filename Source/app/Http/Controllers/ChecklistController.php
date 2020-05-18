<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Enums\ChecklistEnum;
use App\Enums\ChecklistItemTypeEnum;
use App\Helpers\Base64Helper;
use App\Helpers\FileHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Branch;
use App\Models\Checklist\Checklist;
use App\Models\Checklist\ChecklistItemDetail;
use App\Models\Checklist\ChecklistItemType;
use App\Models\Checklist\ChecklistStatus;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Models\Views\CheckListSearch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as ResponseFacades;
use Maatwebsite\Excel\Facades\Excel;

class ChecklistController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $checklist_number = $this->request->get('checklist_number');
        $store_id = Config::get('app.decorcenter_store_id');
        $branch_id = $this->request->get('branch');
        $date_from = $this->request->get('date_from');
        $date_to = $this->request->get('date_to');
        $status = $this->request->get('status');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        $user = Auth::user();
        $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);;
        $role = $user->role->name;

        //get data
        $data = Checklist::Search(
            AccessTypeEnum::Web,
            compact('usb_allowed', 'checklist_number', 'store_id', 'branch_id', 'date_from', 'date_to', 'status', 'role'),
            compact('sort', 'direction')
        );

        $branches = Branch::GetUserBranches($user->user_id, $store_id)->pluck('branch_name', 'branch_id');
        $branches->prepend(trans('global.all_select'), '');

        $status = ChecklistStatus::orderBy('name')->pluck('name', 'id');
        $status->prepend(trans('global.all_select'), '');

        return view('checklist.index')->with(compact('data', 'branches', 'status'));
    }

    public function show($id)
    {
        // Get Data
        $checklist = Checklist::GetView($id);
        $user = Auth::user();

        // Check data if exists
        if (!$checklist) {
            return view('errors.404');
        }

        $list = self::GetData($checklist);

        // Validations
        $error_response = GlobalValidation::CheckUserStoreBranchPermission($user, $checklist->branch_id, null, null, null);

        if($error_response)
            return view('errors.403');

        // Return Data
        return view('checklist.show')->with(compact(
            'checklist',
            'list'
        ));
    }

    public function Export()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $checklist_number = $this->request->get('checklist_number');
        $store_id = Config::get('app.decorcenter_store_id');
        $branch_id = $this->request->get('branch');
        $date_from = $this->request->get('date_from');
        $date_to = $this->request->get('date_to');
        $status = $this->request->get('status');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        $user = Auth::user();
        $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
        $role = $user->role->name;

        //get data
        $data = Checklist::Export(
            compact('usb_allowed', 'checklist_number', 'store_id', 'branch_id', 'date_from', 'date_to', 'status', 'role'),
            compact('sort', 'direction')
        );

        return Excel::create('Checklists', function($excel) use ($data) {

            $excel->sheet('Checklists', function($sheet) use ($data) {
                $sheet->setHeight(array(
                    1     =>  16,
                    2     =>  16
                ));

                $sheet->setFontSize(10);

                $sheet->loadView('templates.excel.checklists')->with(['data' => $data->toArray()]);
                $sheet->setFreeze("A3");
                $sheet->setAutoFilter('A2:S2');

                $sheet->cells('A1:S2', function($cells) {
                    $cells->setAlignment('center');
                });
            });

        })->export('xlsx');

    }

    private static function GetData($checklist){

         //-------------------------
        // BEGIN [TYPES]
        //-------------------------
        $query_to_types = ChecklistItemType::
        select('checklist_item_type.id', 'checklist_item_type.name')->
        with(['sub_types' => function ($query) use ($checklist) {


            //-------------------------
            // BEGIN [SUB_TYPES]
            //-------------------------
            $query_to_subItems = $query->select('checklist_item_type.id', 'checklist_item_type.name', 'checklist_item_type.parent_id');


            //-------------------------
            // BEGIN [ITEMS]
            //-------------------------
            $query_to_subItems->with(['items' => function ($query) use ($checklist) {

                $query_to_items = $query->select([
                    'checklist_item.id',
                    'checklist_item.name',
                    'checklist_item.type',
                    'checklist_item.description',
                    'checklist_item_details.disagreement',
                    'checklist_item_details.disagreement_reason',
                    'checklist_item_details.disagreement_generate_ticket',
                    'checklist_item_details.photo1_guid',
                    'checklist_item_details.photo1_name',
                    'checklist_item_details.photo2_guid',
                    'checklist_item_details.photo2_name',
                    'checklist_item_details.photo3_guid',
                    'checklist_item_details.photo3_name',
                    'checklist_item_details.video_guid',
                    'checklist_item_details.video_name',
                    'checklist_item_details.id as checklist_item_details_id'
                ]);


                // mostrar "solo" los checklist items del checklist relacionado
                $query_to_items
                    ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                    ->join('checklist', 'checklist_item_details.checklist_id', 'checklist.id')
                    ->where('checklist_item_details.checklist_id', $checklist->id);


                $query_to_items->orderBy('checklist_item.display_order', 'asc');

            }]);
            //-------------------------
            // END [ITEMS]
            //-------------------------

            // [PARAM: all] filter for checklist_id
            $query_to_subItems
                ->join('checklist_item', 'checklist_item_type.id', 'checklist_item.type')
                ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                ->where('checklist_item_details.checklist_id', $checklist->id);

            $query_to_subItems
                ->groupBy(['checklist_item_type.id', 'checklist_item_type.name'])
                ->orderBy('checklist_item_type.display_order', 'asc');

            //-------------------------
            // END [SUB_TYPES]
            //-------------------------

        }]);

        // mostrar solo los checklist_item_type del checklist
        $query_to_types
        ->join('checklist_item_type as sub_type', 'checklist_item_type.id', 'sub_type.parent_id')
        ->join('checklist_item', 'sub_type.id', 'checklist_item.type')
        ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
        ->where('checklist_item_details.checklist_id', $checklist->id);


        $data = $query_to_types
            ->where('checklist_item_type.parent_id', null)
            ->groupBy(['checklist_item_type.id', 'checklist_item_type.name'])
            ->orderBy('checklist_item_type.display_order', 'asc')
            ->get();

        //-------------------------
        // END [TYPES]
        //-------------------------

        return $data;
    }


    /* --------------------------------------------- */
    /* AJAX METHODS */
    /* --------------------------------------------- */
    public static function DownloadPhoto($id, $guid)
    {
        try{
            $filename = null;

            $checklist = Checklist::find($id);

            // [Checklist]
            if (!$checklist) {
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $checklist_photo = ChecklistItemDetail::where('checklist_id', $checklist->id)
                ->where('photo1_guid', $guid)
                ->orWhere('photo2_guid', $guid)
                ->orWhere('photo3_guid', $guid)
                ->first();

            if (!$checklist_photo) {
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid de la foto del checklist']),
                ], Response::HTTP_BAD_REQUEST);
            }

            $photo_name = null;

            if($checklist_photo->photo1_guid == $guid) {
                $photo_name = $checklist_photo->photo1_name;

            }else if($checklist_photo->photo2_guid == $guid){
                $photo_name = $checklist_photo->photo2_name;

            }else if($checklist_photo->photo3_guid == $guid){
                $photo_name = $checklist_photo->photo3_name;
            }

            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($photo_name);
            $path = Config::get('app.path_checklist_photos') . $checklist->id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','attachment; filename="' . $photo_name . '"');

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

    public static function DownloadVideo($id, $guid)
    {
        try{
            $filename = null;

            $checklist = Checklist::find($id);

            // [Checklist]
            if (!$checklist) {
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                ], Response::HTTP_NOT_FOUND);
            }

            $guid = StringHelper::Trim($guid);

            $checklist_video = ChecklistItemDetail::where('checklist_id', $checklist->id)
                ->where('video_guid', $guid)
                ->first();

            if (!$checklist_video) {
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Guid del video del checklist']),
                ], Response::HTTP_BAD_REQUEST);
            }

            $video_name = null;

            if($checklist_video->video_guid == $guid) {
                $video_name = $checklist_video->video_name;
            }

            $filename = $guid . '.' . FileHelper::GetExtensionFromFilename($video_name);
            $path = Config::get('app.path_checklist_photos') . $checklist->id . '/' . $filename;

            if (file_exists($path)) {
                $file = File::get($path);
                $type = File::mimeType($path);

                $response = ResponseFacades::make($file, 200);
                $response->header("Content-Type", $type);
                $response->header('Content-disposition','attachment; filename="' . $video_name . '"');

                $return = $response;
            }else{
                return response()->json([
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Video']),
                ], Response::HTTP_BAD_REQUEST);
            }

            return $return;

        }catch(\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al descargar el video'
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public static function UpdateStatus(Request $request)
    {
        $html_data_tab = null;
        $html_history_tab = null;

        // -----------------------------------------------
        // ---------------- Validations ------------------
        // -----------------------------------------------
        if (!$request->id) {
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }

        if (!$request->status_id) {
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }

        $data = Checklist::find($request->id);

        if($data->checklist_status_id != ChecklistEnum::CHECKLIST_STATUS_NEW){
            return response()->json([], Response::HTTP_FORBIDDEN);
        }

        if ($request->status_id == ChecklistEnum::CHECKLIST_STATUS_REJECTED) {
            if(StringHelper::IsNullOrEmptyString($request->status_reason)){
                return response()->json([], Response::HTTP_BAD_REQUEST);
            }
        }

        $user = Auth::user();

        if (!$user->hasRole(['store_manager'])) {
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }

        // -----------------------------------------------
        // ----------------- Save ------------------------
        // -----------------------------------------------
        $data->checklist_status_id = $request->status_id;
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = Carbon::now();

        if ($request->status_id == ChecklistEnum::CHECKLIST_STATUS_APPROVED) {
            $data->approved_by_user = User::GetCreatedByUser($user);
            $data->approved_at = Carbon::now();
        } else if ($request->status_id == ChecklistEnum::CHECKLIST_STATUS_REJECTED) {
            $data->status_reason = $request->status_reason;
            $data->rejected_by_user = User::GetCreatedByUser($user);
            $data->rejected_at = Carbon::now();
        }

        $data->save();

        // -----------------------------------------------
        // -------- Set data and return as html ----------
        // -----------------------------------------------
        $checklist = CheckListSearch::where('checklist_number', $data->checklist_number)->first();
        $list = self::GetData($checklist);

        $html_data_tab = view('checklist.tabs.data-tab', compact('checklist', 'list'))->render();
        $html_history_tab = view('checklist.tabs.history-tab', compact('checklist'))->render();


        return response()->json([
            'html_data_tab' => $html_data_tab,
            'html_history_tab' => $html_history_tab
        ]);
    }

}
