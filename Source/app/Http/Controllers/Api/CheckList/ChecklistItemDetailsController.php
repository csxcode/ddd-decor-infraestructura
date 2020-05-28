<?php namespace App\Http\Controllers\Api\CheckList;

use App\Enums\AccessTypeEnum;
use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\FileHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\Base64Helper;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Transformers\CheckListItemDetailsTransformer;
use App\Http\Controllers\Api\CheckList\Validations\ChecklistItemDetailsValidation;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Http\Controllers\Controller;
use App\Models\Checklist\Checklist;
use App\Models\Checklist\ChecklistItemDetail;
use App\Models\Checklist\ChecklistItemType;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ChecklistItemDetailsController extends Controller
{
    protected $resource = AppObjectNameEnum::CHECKLIST_ITEM_DETAILS;
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
            $checklist = Checklist::find($id);
            $items_request = $this->request->all()['items'];

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = ChecklistItemDetailsValidation::CreateValidation($this->request, $this->resource, $user, $checklist);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            // Add checklist id into array to massive insert
            $items = array_map(function (array $item) use ($checklist) {

                $item['checklist_id'] = $checklist->id;
                $item['checklist_item_id'] = $item['item_id'];

                if($item['disagreement'] == "0"){
                    $item['disagreement_generate_ticket'] = null;
                }

                unset($item['item_id']);
                return $item;
            }, $items_request);

            ChecklistItemDetail::insert($items);

            // save checklist for total_points
            $counter_points_agreeable = ChecklistItemDetail::where('disagreement', '0')->where('checklist_id', $checklist->id)->count();

            $checklist->updated_by_user = User::GetCreatedByUser($user);
            $checklist->updated_at = Carbon::now();
            $checklist->total_points =$counter_points_agreeable;
            $checklist->save();

            DB::commit();

            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST_ITEM_DETAILS,
                    'id' => $checklist->id,
                    'checklist_number' => $checklist->checklist_number,
                    'created_at' => Carbon::parse($checklist->created_at)->timestamp
                ],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();
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

            $user = User::GetByToken($this->request->bearerToken());
            $checklist = Checklist::find($id);

            // --------------------------------------------
            // Get Data From Request
            // --------------------------------------------
            $items = $this->request->all()['items'];


            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = ChecklistItemDetailsValidation::UpdateValidation(
                $this->request, $this->resource, $user, $checklist);

            if ($error_response)
                return $error_response;


            $checklist = Checklist::find($id);
            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::transaction(function () use ($checklist, $user, $items) {

                // [Checklist]
                $checklist->updated_by_user = User::GetCreatedByUser($user);
                $checklist->updated_at = Carbon::now();
                $checklist->save();


                // [Items]
                if (isset($items)) {
                    if (count($items) > 0) {
                        foreach ($items as $item) {

                            $checklist_item_detail = ChecklistItemDetail::where('checklist_id', $checklist->id)
                                ->where('checklist_item_id', $item['item_id'])->first();

                            // Check if does not exists
                            if (!$checklist_item_detail) {
                                $checklist_item_detail = new ChecklistItemDetail();
                                $checklist_item_detail->checklist_id = $checklist->id;
                                $checklist_item_detail->checklist_item_id = $item['item_id'];
                            }

                            // Check operation
                            if ($item['disagreement'] == 1) {

                                $checklist_item_detail->disagreement = 1;
                                $checklist_item_detail->disagreement_reason = $item['disagreement_reason'];
                                $checklist_item_detail->disagreement_generate_ticket = $item['disagreement_generate_ticket'];

                            } else {

                                $checklist_item_detail->disagreement = 0;
                                $checklist_item_detail->disagreement_reason = $item['disagreement_reason'];
                                $checklist_item_detail->disagreement_generate_ticket = null;
                            }

                            // Save data (new or update)
                            $checklist_item_detail->save();

                        }
                    }
                }

                // save again checklist for total_points
                $counter_points_agreeable = ChecklistItemDetail::where('disagreement', '0')->where('checklist_id', $checklist->id)->count();
                $checklist->total_points =$counter_points_agreeable;
                $checklist->save();

            });


            // --------------------------------------------
            // Return Data
            // --------------------------------------------

            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST,
                    'id' => $checklist->id,
                    'updated_at' => ($checklist->updated_at == null ? null : Carbon::parse($checklist->updated_at)->timestamp)
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function Get($id)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($this->request->bearerToken());
        $role = $user->role->name;
        $query = Checklist::find($id);

        $query = Checklist::where('checklist.id', $id);

        if (isset($role) && !StringHelper::IsNullOrEmptyString($role)) {
            if (strtolower($role) != strtolower(UserRoleEnum::VISUAL)) {
                $query->where('edit_status', 1);
            }
        }

        $checklist = $query->first();

        // --------------------------------------------
        // Validations
        // --------------------------------------------
        $error_response = ChecklistItemDetailsValidation::GetValidation($this->request, $this->resource, $user, $checklist);

        if($error_response)
            return $error_response;

        try {

            //-------------------------------------------
            // Get data
            // ------------------------------------------
            $data_to_return = self::GetDataForGet($user, $checklist, $this->request);

            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST_ITEM_DETAILS,
                    'types' => $data_to_return
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }
    }

    public function PhotoProcess($id, $item_id)
    {
        // Check if json is valid
        if(!$this->request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $checklist = Checklist::where('id', $id)->first();
            $photos = null;

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = ChecklistItemDetailsValidation::PhotoProcessValidation($this->request, $this->resource, $user, $checklist, $item_id, $photos);

            if($error_response)
                return $error_response;

            // Check if the folder is already created otherwise will be create
            $folder_path = Config::get('app.path_checklist_photos') . $checklist->id . '/';
            if (!file_exists($folder_path)) {
                mkdir($folder_path, 0777, true);
            }


            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            $files_to_delete = [];
            $photos_processed = [];

            foreach($photos as $photo) {

                array_push($photos_processed, 'photo'.$photo['order']);

                $field_name_photo_guid = 'photo'.$photo['order'].'_guid';
                $field_name_photo_name = 'photo'.$photo['order'].'_name';

                $checklist_item_detail = ChecklistItemDetail::where('checklist_id', $checklist->id)
                    ->where('checklist_item_id', $item_id)->first();

                $checklist_item_detail_clone = clone $checklist_item_detail;

                if($photo['action'] == ActionEnum::DELETE) {

                    // update data
                    $checklist_item_detail->update(
                        [
                            $field_name_photo_guid => null,
                            $field_name_photo_name => null
                        ]
                    );

                    // add to global array to delete
                    $path_file_to_delete = $folder_path . $checklist_item_detail_clone[$field_name_photo_guid] . '.' . FileHelper::GetExtensionFromFilename($checklist_item_detail_clone[$field_name_photo_name]);
                    array_push($files_to_delete, $path_file_to_delete);

                }else {

                    // update data
                    $guid = FunctionHelper::CreateGUID(16);

                    $checklist_item_detail->update(
                        [
                            $field_name_photo_guid => $guid,
                            $field_name_photo_name => $photo['name']
                        ]
                    );

                    // save image to disk
                    $extension = FileHelper::GetExtensionFromFilename($photo['name']);
                    $safeName = $guid . '.' . $extension;
                    file_put_contents($folder_path . $safeName, base64_decode($photo['base64']));

                    // add to global array to delete
                    if($checklist_item_detail_clone[$field_name_photo_guid] != null){
                        $path_file_to_delete = $folder_path . $checklist_item_detail_clone[$field_name_photo_guid] . '.' . FileHelper::GetExtensionFromFilename($checklist_item_detail_clone[$field_name_photo_name]);
                        array_push($files_to_delete, $path_file_to_delete);
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
                    'object' => AppObjectNameEnum::CHECKLIST_ITEM_DETAILS,
                    'id' => $checklist->id,
                    'response' => [
                        'message' => 'Fotos procesadas correctamente.',
                        'photos_processed' => $photos_processed
                    ]
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

    }

    public function VideoProcess($id, $item_id)
    {

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $checklist = Checklist::find($id);
            $data = [];

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = ChecklistItemDetailsValidation::VideoProcessValidation($this->request, $this->resource, $user, $checklist, $item_id, $data);

            if($error_response)
                return $error_response;

            // Check if the folder is already created otherwise will be create
            $folder_path = Config::get('app.path_checklist_photos') . $checklist->id . '/';
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

                // get data
                $checklist_item_detail = ChecklistItemDetail::where('checklist_id', $checklist->id)
                    ->where('checklist_item_id', $item_id)->first();

                // clone data
                $checklist_item_detail_clone = clone $checklist_item_detail;


                if($item['action'] == ActionEnum::DELETE) {

                    // update data
                    $checklist_item_detail->update(
                        [
                            'video_guid' => null,
                            'video_name' => null
                        ]
                    );

                    // add to global array to delete
                    $path_file_to_delete = $folder_path . $checklist_item_detail_clone['video_guid'] . '.' . FileHelper::GetExtensionFromFilename($checklist_item_detail_clone['video_name']);
                    array_push($files_to_delete, $path_file_to_delete);

                    $message = 'video was delete successfully';

                }else {

                    // update data
                    $guid = FunctionHelper::CreateGUID(16);

                    $checklist_item_detail->update(
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
                        $path_file_to_delete = $folder_path . $checklist_item_detail_clone['video_guid'] . '.' . FileHelper::GetExtensionFromFilename($checklist_item_detail_clone['video_name']);
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
                    'object' => AppObjectNameEnum::CHECKLIST_ITEM_DETAILS,
                    'id' => $checklist->id,
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

    private static function GetDataForGet($user, $checklist, $request)
    {
        $usb_allowed = null;
        $sub_type = $request->exists('sub_type');
        $item_id = $request->exists('item_id');
        $all = $request->exists('all');

        if($sub_type){
            $sub_type = StringHelper::Trim($request->get('sub_type'));
        }else{
            $sub_type = null;
        }

        if($item_id){
            $item_id = StringHelper::Trim($request->get('item_id'));
        }else{
            $item_id = null;
        }

        if($all){
            $all = StringHelper::Trim($request->get('all'));
        }else{
            $all = 0;
        }

        // check if user needs filter by branch
        if(GlobalValidation::UserNeedToFilterData($user)) {
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
        }

        // ============================================================================
        // Apply filter to get data
        // ============================================================================

        //-------------------------
        // BEGIN [TYPES]
        //-------------------------
        $query_to_types = ChecklistItemType::
        select('checklist_item_type.id', 'checklist_item_type.name')->
        with(['sub_types' => function ($query) use ($usb_allowed, $checklist, $sub_type, $item_id, $all) {


            //-------------------------
            // BEGIN [SUB_TYPES]
            //-------------------------
            $query_to_subItems = $query->select('checklist_item_type.id', 'checklist_item_type.name', 'checklist_item_type.parent_id');


            //-------------------------
            // BEGIN [ITEMS]
            //-------------------------
            $query_to_subItems->with(['items' => function ($query) use ($usb_allowed, $checklist, $item_id, $all) {

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


                // [PARAM: all] filter for checklist_id
                if($all == 0){

                    // mostrar "solo" los checklist items del checklist relacionado

                    $query_to_items
                        ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                        ->join('checklist', 'checklist_item_details.checklist_id', 'checklist.id')
                        ->where('checklist_item_details.checklist_id', $checklist->id);

                    if ($usb_allowed != null) {
                        $query_to_items->whereIn('checklist.branch_id', $usb_allowed);
                    }

                } else {

                    // mostrar "todos" los checklist items y con datos de los relacionados

                    $query_to_items->leftJoin('checklist_item_details', function($join) use ($checklist) {
                        $join->on('checklist_item.id', 'checklist_item_details.checklist_item_id');
                        $join->on('checklist_item_details.checklist_id', DB::raw($checklist->id));
                    });

                     // aplica la regla de status (enabled) a la tabla checklist_item
                     $query_to_items->where('checklist_item.item_status', 1);
                }


                if($item_id != null){
                    $query_to_items->where('checklist_item.id', $item_id);
                }

                $query_to_items->orderBy('checklist_item.display_order', 'asc');

            }]);
            //-------------------------
            // END [ITEMS]
            //-------------------------

            // [PARAM: all] filter for checklist_id
            if($all == 0) {

                $query_to_subItems
                    ->join('checklist_item', 'checklist_item_type.id', 'checklist_item.type')
                    ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                    ->where('checklist_item_details.checklist_id', $checklist->id);

            } else {

                 // aplica la regla de status (enabled) a la tabla checklist_item_type (sub_types = child)
                 $query_to_subItems->where('type_status', 1);

            }


            if($sub_type != null){
                $query_to_subItems->where('checklist_item_type.id', $sub_type);
            }

            $query_to_subItems
                ->groupBy(['checklist_item_type.id', 'checklist_item_type.name'])
                ->orderBy('checklist_item_type.display_order', 'asc');

            //-------------------------
            // END [SUB_TYPES]
            //-------------------------

        }]);


        // [PARAM: all] filter for checklist_id
        if($all == 0) {

            // mostrar solo los checklist_item_type del checklist
            $query_to_types
                ->join('checklist_item_type as sub_type', 'checklist_item_type.id', 'sub_type.parent_id')
                ->join('checklist_item', 'sub_type.id', 'checklist_item.type')
                ->join('checklist_item_details', 'checklist_item.id', 'checklist_item_details.checklist_item_id')
                ->where('checklist_item_details.checklist_id', $checklist->id);

        } else {

            // aplica la regla de status (enabled) a la tabla checklist_item_type (types = parent)
            $query_to_types->where('checklist_item_type.type_status', 1);

        }

        $data = $query_to_types
            ->where('checklist_item_type.parent_id', null)
            ->groupBy(['checklist_item_type.id', 'checklist_item_type.name'])
            ->orderBy('checklist_item_type.display_order', 'asc')
            ->get()
            ->toArray();

        //-------------------------
        // END [TYPES]
        //-------------------------

        return (new CheckListItemDetailsTransformer)->all($data);
    }
}
