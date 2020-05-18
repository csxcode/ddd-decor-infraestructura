<?php namespace App\Http\Controllers\Api\CheckList;

use App\Emails\ChecklistEmail;
use App\Enums\AccessTypeEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\ChecklistEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonHelper;
use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\CheckList\Validations\ChecklistValidation;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Http\Controllers\Controller;
use App\Models\Checklist\Checklist;
use App\Models\Ticket\Ticket;
use App\Models\User;
use App\Models\UserStoreBranch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ChecklistController extends Controller
{
    protected $resource = AppObjectNameEnum::CHECKLIST;
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

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = ChecklistValidation::CreateValidation($this->request, $this->resource, $user);

            if($error_response)
                return $error_response;

            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            $checklist = Checklist::create([
                'checklist_number' => Checklist::GenerateNumber(),
                'checklist_status_id' => ChecklistEnum::CHECKLIST_STATUS_NEW,
                'branch_id' => StringHelper::Trim($this->request->get('branch_id')),
                'created_at' => Carbon::now(),
                'created_by_user' => User::GetCreatedByUser($user),
                'created_by_user_id' => $user->user_id,
                'edit_status' => 0,
                'updated_at' => null
            ]);

            DB::commit();

            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST,
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

    public function Get($id)
    {
        try {
            $user = User::GetByToken($this->request->bearerToken());
            $role = $user->role->name;      

            $columns = '
                checklist.id,
                checklist.checklist_number,
                checklist.checklist_status_id,
                checklist_status.name as status_name,                         
                branch.branch_id,
                branch.name as branch_name,
                store.store_id,
                store.name as store_name,
                checklist.status_reason,
                checklist.created_by_user,
                checklist.created_at,
                checklist.updated_by_user,
                checklist.updated_at,  
                checklist.approved_by_user,  
                checklist.approved_at,  
                checklist.rejected_by_user,
                checklist.rejected_at,
                checklist.total_points            
            ';

            $columns = ltrim(rtrim($columns));

            $query = Checklist::select(DB::raw($columns))
                ->leftJoin('branch', 'checklist.branch_id', 'branch.branch_id')
                ->leftJoin('store', 'branch.store_id', 'store.store_id')
                ->leftJoin('checklist_status', 'checklist.checklist_status_id', 'checklist_status.id')
                ->where('checklist.id', $id);

            if (isset($role) && !StringHelper::IsNullOrEmptyString($role)) {                       
                if (strtolower($role) != strtolower(UserRoleEnum::VISUAL)) {                                    
                    $query->where('edit_status', 1);
                }
            }

            $checklist = $query->first();

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            if (!$checklist) {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.not_exists', ['attribute' => 'Checklist']),
                    'error_code' => ErrorCodesEnum::not_exists,
                    'error_data' => [
                        'resource' => $this->resource,
                        'field' => 'id'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }


            // [Check UserStoreBrand Permission ]
            $error_response = GlobalValidation::CheckUserStoreBranchPermission(
                $user, $checklist->branch_id,
                trans('api/validation.forbidden_entity_sb', ['entity' => 'checklist']),
                $this->resource, 'id');

            if($error_response)
                return $error_response;


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::CHECKLIST,
                    'id' => $checklist->id,
                    'checklist_number' => $checklist->checklist_number,
                    'status' => $checklist->checklist_status_id,
                    'status_name' => $checklist->status_name,
                    'status_reason' => $checklist->status_reason,
                    'branch_id' => $checklist->branch_id,
                    'branch_name' => $checklist->branch_name,
                    'store_id' => $checklist->store_id,
                    'store_name' => $checklist->store_name,
                    'created_by_user' => $checklist->created_by_user,
                    'created_at' => Carbon::parse($checklist->created_at)->timestamp,
                    'updated_by_user' => $checklist->updated_by_user,
                    'updated_at' => ($checklist->updated_at == null ? null : Carbon::parse($checklist->updated_at)->timestamp),
                    'approved_by_user' => $checklist->approved_by_user,
                    'approved_at' => ($checklist->approved_at == null ? null : Carbon::parse($checklist->approved_at)->timestamp),
                    'rejected_by_user' => $checklist->rejected_by_user,
                    'rejected_at' => ($checklist->rejected_at == null ? null : Carbon::parse($checklist->rejected_at)->timestamp),
                    'total_points' => $checklist->total_points
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
            $status = StringHelper::Trim($this->request->get('status'));
            $disagreement = StringHelper::Trim($this->request->get('disagreement'));
            $date_from = StringHelper::Trim($this->request->get('date_from'));
            $date_to = StringHelper::Trim($this->request->get('date_to'));
            $per_page = StringHelper::Trim($this->request->get('per_page'));
            $page = StringHelper::Trim($this->request->get('page'));
            $role = $user->role->name;                                    

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = ChecklistValidation::AllValidation($this->request, $this->resource);

            if($error_response)
                return $error_response;

            // Set values by default
            PaginateHelper::SetPaginateDefaultValues($page, $per_page);

            // --------------------------------------------
            // Get Data
            // --------------------------------------------
            $usb_allowed = null;

            if(GlobalValidation::UserNeedToFilterData($user)){
                $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            }

            $data = Checklist::Search(
                AccessTypeEnum::Api,
                compact('usb_allowed', 'store_id', 'branch_id', 'status', 'disagreement', 'date_from', 'date_to', 'per_page', 'page', 'role'),
                ['sort' => 'checklist_number', 'direction' => 'desc']
            );


            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                PaginateHelper::TransformPaginateData($this->resource, 'checklist', $data),
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

            $user = User::GetByToken($this->request->bearerToken());
            $checklist = Checklist::find($id);                                      
            $checklist_clone = null;
            $changes = null;

            if($checklist){
                $checklist_clone = clone $checklist;                
            }

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = CheckListValidation::UpdateValidation(
                $this->request, $this->resource, $user,
                $checklist, $changes);

            if ($error_response)
                return $error_response;


            // --------------------------------------------
            // Save
            // --------------------------------------------

            // update only if there are changes
            if ($changes) {                

                DB::transaction(function () use ($checklist, $user, $changes) {

                    // [Checklist]
                    $checklist->updated_by_user = User::GetCreatedByUser($user);
                    $checklist->updated_at = Carbon::now();
                    $checklist->save();

                    // Generate Tickets
                    try{

                        if(isset($changes['checklist_status_id'])){
                            if($changes['checklist_status_id'] == ChecklistEnum::CHECKLIST_STATUS_APPROVED){

                                $user_that_created_checklist = User::with('role')->where('user_id', $checklist->created_by_user_id)->first();

                                if(!$user_that_created_checklist)
                                    $user_that_created_checklist = $user;

                                Ticket::CreateTicketsFromChecklist($checklist, $user_that_created_checklist);
                            }
                        }

                    } catch (\Exception $e) {
                        ErrorHelper::SendInternalErrorMessageForApi($e);
                    }

                });

                //------------------------------------------------------------------------------------
                // Notificación email cuando el registro checklist es completado
                //------------------------------------------------------------------------------------
                if(isset($changes['edit_status'])){

                    // Si el valor del campo edit_status está cambiado de 0 a 1 (edición completada), se debe enviar una notificación email
                    if($checklist_clone->edit_status == ChecklistEnum::EDIT_STATUS_EDITING && $changes['edit_status'] == ChecklistEnum::EDIT_STATUS_COMPLETED){
                        ChecklistEmail::NotifyWhenChecklistWasCompleted($checklist->id);
                    }
                }

                //------------------------------------------------------------------------------------
                // Notificación email cuando el checklist ha sido aprobado o rechazado
                //------------------------------------------------------------------------------------
                if(isset($changes['checklist_status_id'])){

                    // Si el valor del campo checklist_status_id cambia de 1 (Nuevo) a alguno de estos dos estados:
                    // 2 (Aprobado) 
                    // 3 (Rechazado)

                    if($checklist_clone->checklist_status_id == ChecklistEnum::CHECKLIST_STATUS_NEW && 
                        ($changes['checklist_status_id'] == ChecklistEnum::CHECKLIST_STATUS_APPROVED || $changes['checklist_status_id'] ==  ChecklistEnum::CHECKLIST_STATUS_REJECTED)
                    ){
                        ChecklistEmail::NotifyWhenChecklistWasApprovedOrRejected($checklist->id);
                    }
                }
            }


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

}