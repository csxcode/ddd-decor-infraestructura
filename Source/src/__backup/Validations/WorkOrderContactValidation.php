<?php namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderContact;
use App\Validations\BaseValidation;
use App\Validations\WorkOrderValidation;
use Illuminate\Http\Response;

class WorkOrderContactValidation extends BaseValidation
{
    public $workOrderContact;
    protected $workOrderValidation;

    public function __construct(WorkOrderValidation $workOrderValidation)
    {
        $this->workOrderValidation = $workOrderValidation;
    }

    public function store($request, $resource, $woID, &$save)
    {
        // --------------------------------------------
        // Check if json is valid
        // --------------------------------------------
        if(!$request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();


        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);

        $data = $request->get('data');
        $workOrder = WorkOrder::find($woID);

        $save = [];


        // ------------------------------------------------------------------
        // ======================= work_order_id ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ========================== data ==================================
        // ------------------------------------------------------------------
        $error_response = $this->checkArrayLength($resource, $data, 'data', trans('api/validation.array_no_one', ['attribute' => 'contactos']));

        if($error_response)
            return $error_response;

        foreach ($data as $item)
        {
            $userId =  array_key_exists('user_id', $item) ? StringHelper::Trim($item['user_id']) : null;
            $field = 'user_id (' . $userId . ')';

            // check required
            $error_response = $this->checkValueMustBeRequired($resource, $userId, 'user_id');

            if($error_response)
                return $error_response;

            // check related
            $error_response = $this->checkExistsContactUserRelated($resource, $workOrder->id, $userId, $field);

            if($error_response)
                return $error_response;

            # Add data to then create in mass
            $contact = [
              'work_order_id' => $workOrder->id,
              'user_id' => $userId
            ];

            array_push($save, $contact);
        }

        // Check duplicates
        $error_response = $this->checkDuplicatesArrayByField($resource, $data, 'user_id', 'user_id', trans('api/validation.duplicated', ['attribute' => 'user_id']));

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        return null;
    }

    public function destroy($request, $resource, $woID, $contactId)
    {
        // --------------------------------------------
        // Get Data
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $workOrder = WorkOrder::find($woID);
        $workOrderContact = WorkOrderContact::where('work_order_id', $woID)->where('user_id', $contactId)->first();

        // ------------------------------------------------------------------
        // ======================= Check Role ===============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ================== work_order_contact ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkWorkOrderContact($resource, $workOrderContact);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->workOrderContact = $workOrderContact;

        return null;
    }

    public function index($request, $resource, $woID, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $workOrder = WorkOrder::find($woID);

        $per_page = StringHelper::Trim($request->get('per_page'));
        $page = StringHelper::Trim($request->get('page'));

        $params = [
            'user' => $user,
            'work_order_id' => null,
            'per_page' => $per_page,
            'page' => $page,
        ];


        // ------------------------------------------------------------------
        // ========================== work_order ============================
        // ------------------------------------------------------------------
        $error_response = $this->workOrderValidation->checkWorkOrder($resource, $workOrder);

        if($error_response)
            return $error_response;

        $params['work_order_id'] = $workOrder->id;

        // ------------------------------------------------------------------
        // ========================= Pagination =============================
        // ------------------------------------------------------------------
        $error_response = $this->checkPagination($resource, $page, $per_page);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        return null;
    }


    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------
    public function checkExistsContactUserRelated($resource, $woID, $userID, $field)
    {
         // Validar que no haya contacto (user_id) duplicado dentro de una work order
         $count_woc = WorkOrderContact::where('work_order_id', $woID)->where('user_id', $userID)->count();

         if($count_woc > 0){

             return response()->json([
                 'object' => AppObjectNameEnum::ERROR,
                 'message' => trans('api/validation.already_exists', ['attribute' => $field]),
                 'error_code' => ErrorCodesEnum::already_exists,
                 'error_data' => [
                     'resource' => $resource,
                     'field' => $field
                 ]
             ], Response::HTTP_BAD_REQUEST);

         }


    }

    public function checkWorkOrderContact($resource, $workOrderContact)
    {
        if (!$workOrderContact) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'work_order_contact']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }
    }

}
