<?php
namespace App\Validations;

use App\Enums\ActionEnum;
use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\MaintenanceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Models\Maintenance\Maintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;

class MaintenanceValidation extends BaseValidation
{
    public $maintenance;
    public $user;

    public function store($request, $resource, &$save)
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

        $branch_location_id = StringHelper::Trim($request->get('branch_location'));
        $maintenance_title = StringHelper::Trim($request->get('title'));
        $maintenance_date = StringHelper::Trim($request->get('date'));
        $description = StringHelper::Trim($request->get('description'));

        $save = [
            'branch_location_id' => null,
            'maintenance_title' => null,
            'maintenance_date' => null,
            'description' => null,
        ];


        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::CREATE);

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // =================== branch_location_id ===========================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $branch_location_id, 'branch_location');

        if($error_response)
            return $error_response;

        $error_response = BranchLocationValidation::checkBranchLocation($resource, $branch_location_id, $user, 'branch_location');

        if($error_response)
            return $error_response;

        $save['branch_location_id'] = $branch_location_id;


        // ------------------------------------------------------------------
        // =================== maintenance_title ============================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $maintenance_title, 'title');

        if($error_response)
            return $error_response;

        $save['maintenance_title'] = $maintenance_title;


        // ------------------------------------------------------------------
        // =================== maintenance_date =============================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $maintenance_date, 'date');

        if($error_response)
            return $error_response;

        $error_response = $this->checkDateTimestampIsValid($resource, $maintenance_date, 'date');

        if($error_response)
            return $error_response;

        $save['maintenance_date'] = Carbon::createFromTimestamp($maintenance_date);


        // ------------------------------------------------------------------
        // =================== $description =================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueMustBeRequired($resource, $description, 'description');

        if($error_response)
            return $error_response;

        $save['description'] = $description;


        // ------------------------------------------------------------------
        // ================ Check fields excluded ===========================
        // ------------------------------------------------------------------
        $excludedFields = ['maintenance_number', 'status_id'];

        $error_response = $this->checkExcludedFields($resource, $request->all(), $excludedFields);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;
        return null;
    }

    public function update($request, $resource, $id, &$changes)
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
        $maintenance = Maintenance::find($id);

        $branch_location_id = StringHelper::Trim($request->get('branch_location'));
        $maintenance_title = StringHelper::Trim($request->get('title'));
        $maintenance_date = StringHelper::Trim($request->get('date'));
        $description = StringHelper::Trim($request->get('description'));

        $maintenanceClone = null;

        // ------------------------------------------------------------------
        // ==================== maintenance =================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueHasData($resource, $maintenance, 'mantenimiento (id)');

        if($error_response) {
            return $error_response;
        } else {
            $maintenanceClone = clone $maintenance;
        }

        $error_response = $this->checkCanBeUpdatedByStatus($resource, $maintenance);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::EDIT);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // =================== branch_location_id ===========================
        // ------------------------------------------------------------------
        if($request->exists('branch_location')) {
            $error_response = $this->checkValueMustBeRequired($resource, $branch_location_id, 'branch_location');

            if($error_response)
                return $error_response;

            $error_response = BranchLocationValidation::checkBranchLocation($resource, $branch_location_id, $user, 'branch_location');

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('branch_location_id', $maintenanceClone->branch_location_id, $branch_location_id, $maintenance, $changes);
        }


        // ------------------------------------------------------------------
        // =================== maintenance_title ============================
        // ------------------------------------------------------------------
        if($request->exists('title')) {
            $error_response = $this->checkValueMustBeRequired($resource, $maintenance_title, 'title');

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('maintenance_title', $maintenanceClone->maintenance_title, $maintenance_title, $maintenance, $changes);
        }


        // ------------------------------------------------------------------
        // =================== maintenance_date =============================
        // ------------------------------------------------------------------
        if($request->exists('date')) {
            $error_response = $this->checkValueMustBeRequired($resource, $maintenance_date, 'date');

            if($error_response)
                return $error_response;

            $error_response = $this->checkDateTimestampIsValid($resource, $maintenance_date, 'date');

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('maintenance_date', $maintenanceClone->maintenance_date, Carbon::createFromTimestamp($maintenance_date), $maintenance, $changes);
        }


        // ------------------------------------------------------------------
        // =================== $description =================================
        // ------------------------------------------------------------------
        if($request->exists('description')) {
            $error_response = $this->checkValueMustBeRequired($resource, $description, 'description');

            if($error_response)
                return $error_response;

            $this->checkThereIsChangesAndSetToModel('description', $maintenanceClone->description, $description, $maintenance, $changes);
        }


        // ------------------------------------------------------------------
        // ================ Check fields excluded ===========================
        // ------------------------------------------------------------------
        $excludedFields = ['maintenance_number', 'status_id'];

        $error_response = $this->checkExcludedFields($resource, $request->all(), $excludedFields);

        if($error_response)
            return $error_response;

        // ------------ Return ----------------
        //--------------------------------------
        $this->maintenance = $maintenance;
        $this->user = $user;

        return null;
    }

    public function destroy($request, $resource, $id)
    {
        // --------------------------------------------
        // Get Data
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $maintenance = Maintenance::find($id);


        // ------------------------------------------------------------------
        // ==================== maintenance =================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueHasData($resource, $maintenance, 'maintenimiento (id)');

        if($error_response)
            return $error_response;

        $error_response = $this->checkCanBeDeletedByStatus($resource, $maintenance);

        if($error_response)
            return $error_response;

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::DELETE);

        if($error_response)
            return $error_response;


        // ------------ Return ----------------
        //--------------------------------------
        $this->maintenance = $maintenance;

        return null;
    }

   public function index($request, $resource, &$params)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);

        $status_id = StringHelper::Trim($request->get('status_id'));
        $branch_id = StringHelper::Trim($request->get('branch_id'));
        $date_from = StringHelper::Trim($request->get('date_from'));
        $date_to = StringHelper::Trim($request->get('date_to'));
        $per_page = StringHelper::Trim($request->get('per_page'));
        $page = StringHelper::Trim($request->get('page'));

        $params = [
            'user' => $user,
            'branch_id' => $branch_id,
            'status_id' => $status_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'per_page' => $per_page,
            'page' => $page,
        ];

        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::LIST);

        if($error_response)
            return $error_response;


        // ======================== Date From ================================
        if (!StringHelper::IsNullOrEmptyString($date_from)) {
            $error_response = $this->checkDateTimestampIsValid($resource, $date_from, 'date_from');

            if ($error_response)
                return $error_response;
        }

        // ======================== Date To ================================
        if (!StringHelper::IsNullOrEmptyString($date_to)) {
            $error_response = $this->checkDateTimestampIsValid($resource, $date_to, 'date_to');

            if ($error_response)
                return $error_response;
        }

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

    public function show($request, $resource, $id)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $user = User::GetByToken($request->bearerToken());
        $role = strtolower($user->role->name);
        $maintenance = Maintenance::find($id);

        // ------------------------------------------------------------------
        // ==================== maintenance =================================
        // ------------------------------------------------------------------
        $error_response = $this->checkValueHasData($resource, $maintenance, 'id');

        if($error_response)
            return $error_response;


        // ------------------------------------------------------------------
        // ============================ role ================================
        // ------------------------------------------------------------------
        $error_response = $this->checkRoleIsAllowed($resource, $role, ActionEnum::VIEW);

        if($error_response)
            return $error_response;

        // ------------ Return ----------------
        //--------------------------------------
        $this->user = $user;

        return null;
    }

    // ------------------------------------------------------------------
    // ====================== Check Functions ===========================
    // ------------------------------------------------------------------
    public static function checkRoleIsAllowed($resource, $role, $action)
    {
        if ($action == ActionEnum::CREATE ||
            $action == ActionEnum::EDIT ||
            $action == ActionEnum::DELETE ||
            $action == ActionEnum::VIEW ||
            $action == ActionEnum::LIST) {

            if ($role != UserRoleEnum::GESTOR_INFRAESTRUCTURA)
            {
                return response()->json([
                    'object' => AppObjectNameEnum::ERROR,
                    'message' => trans('api/validation.forbidden_role_user'),
                    'error_code' => ErrorCodesEnum::forbidden,
                    'error_data' => [
                        'resource' => $resource,
                        'field' => 'user'
                    ]
                ], Response::HTTP_FORBIDDEN);
            }

        }

        return null;
    }

    public function checkCanBeUpdatedByStatus($resource, $maintenance)
    {
        // Tener en cuenta que no debe permitir actualizar los mantenimientos con estado iniciado (2) y completado (3).
        if ($maintenance->status_id == MaintenanceStatusEnum::STARTED || $maintenance->status_id == MaintenanceStatusEnum::COMPLETED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.maintenance.cannot_update_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }
    }

    public function checkCanBeDeletedByStatus($resource, $maintenance)
    {
        // Solo si el estado es Pendiente (1), puede ser eliminado el mantenimiento.
        if ($maintenance->status_id != MaintenanceStatusEnum::PENDING) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.maintenance.cannot_delete_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }
    }
}
