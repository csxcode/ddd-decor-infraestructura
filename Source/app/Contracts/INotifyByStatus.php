<?php
namespace App\Contracts;

use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderHistory;

interface INotifyByStatus
{
    public function send(WorkOrderHistory $workOrderHistory);
}
