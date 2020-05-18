<?php

namespace App\Services;

use App\Enums\ActionEnum;
use App\Models\User;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderStatus;

class WorkOrderService
{
    protected $workOrder;    
        
    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;        
    }

    public function generateNumber(){
        $ini_counter = 1000;
        $max = $this->workOrder::max('wo_number');
        if(!$max)
            $max = $ini_counter;
        $max = ((int)$max + 1);
        return $max;
    }   

    public function create($data, $user)
    {
        $data['wo_number'] = $this->generateNumber();
        $data['work_order_status_id'] = WorkOrderStatus::STATUS_COTIZANDO;
        $data['created_at'] = now();
        $data['created_by_user'] = User::GetCreatedByUser($user);
        $data['updated_at'] = null;
       
        return $this->workOrder->create($data);        
    }
    
    public function update(WorkOrder $data, $user)
    {      
        $data->updated_by_user = User::GetCreatedByUser($user);
        $data->updated_at = now();
        $data->save();            

        return $data;
    }

}
