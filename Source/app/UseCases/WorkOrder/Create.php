<?php

namespace App\UseCases\WorkOrder;

use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderStatus;
use App\Services\WorkOrderService;

class Create
{
    public function execute($data, $user)
    {
        $data['wo_number'] = (new WorkOrderService)->generateNumber();
        $data['work_order_status_id'] = WorkOrderStatus::STATUS_COTIZANDO;
        $data['created_at'] = now();
        $data['created_by_user'] = User::GetCreatedByUser($user);
        $data['updated_at'] = null;

        return WorkOrder::create($data);
    }
}
