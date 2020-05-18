<?php
namespace App\Services;

use App\Enums\MaintenanceStatusEnum;
use App\Models\Maintenance\Maintenance;
use App\Models\User;

class MaintenanceService
{
    private $maintenanceRepository;

    public function __construct(Maintenance $maintenanceRepository)
    {
        $this->maintenanceRepository = $maintenanceRepository;
    }

    public function store(array $data, User $user) : Maintenance
    {
        $data['maintenance_number'] = $this->maintenanceRepository->generateNumber();
        $data['status_id'] = MaintenanceStatusEnum::PENDING;
        $data['created_at'] = now();
        $data['created_by_user'] = User::GetCreatedByUser($user);
        $data['updated_at'] = null;

        return $this->maintenanceRepository->create($data);
    }

    public function update(Maintenance $data, User $user) : Maintenance
    {
        $data->updated_at = now();
        $data->updated_by_user =  User::GetCreatedByUser($user);;
        $save = $data->save();

        if(!$save) {
            throw new \ErrorException('could not be updated');
        }

        return $data;
    }

    public function destroy(Maintenance $data) : void
    {
        $wasDeleted = $data->delete();

        if(!$wasDeleted) {
            throw new \ErrorException('could not be deleted');
        }
    }

    public function show($id, $user)
    {
        $columns = '
            maintenance.id,
            maintenance.maintenance_number,
            maintenance.maintenance_title,
            maintenance.maintenance_date,
            maintenance.description,
            maintenance.reminder1,
            maintenance.reminder2,
            maintenance.created_by_user,
            maintenance.created_at,
            maintenance.updated_by_user,
            maintenance.updated_at,

            maintenance_status.id as status_id,
            maintenance_status.name as status_name,

            work_order.id as work_order_id,
            work_order.wo_number,

            branch_location.name as branch_location_name,
            branch.branch_id,
            branch.name as branch_name
        ';

        $columns = ltrim(rtrim($columns));

        $query = $this->maintenanceRepository->select(\DB::raw($columns))
            ->leftJoin('maintenance_status', 'maintenance.status_id', 'maintenance_status.id')
            ->leftJoin('work_order', 'maintenance.id', 'work_order.maintenance_id')
            ->leftJoin('branch_location', 'maintenance.branch_location_id', 'branch_location.id')
            ->leftJoin('branch', 'branch_location.branch_branch_id', 'branch.branch_id')
            ->where('maintenance.id', $id);

        $this->maintenanceRepository->scopeFilterByRole($query, $user);

        return $query->first();
    }
}
