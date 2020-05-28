<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkOrder\WorkOrder;

class WorkOrderService
{
    public function generateNumber()
    {
        $ini_counter = 1000;
        $max = WorkOrder::max('wo_number');
        if (!$max)
            $max = $ini_counter;
        $max = ((int) $max + 1);
        return $max;
    }

    public function update(WorkOrder $data, $user)
    {
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();

        return $data;
    }
}
