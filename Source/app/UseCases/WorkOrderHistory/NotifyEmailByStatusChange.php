<?php
namespace App\UseCases\WorkOrderHistory;

use App\Factories\WorkOrderHistory\NotificationByStatusFactory;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderHistory;

class NotifyEmailByStatusChange
{
    public function run(WorkOrderHistory $workOrderHistory, WorkOrder $oldWorkOrder)
    {
        $notificationByStatusFactory = new NotificationByStatusFactory();
        $notification = $notificationByStatusFactory->initialize($workOrderHistory->work_order_status_id, $oldWorkOrder);

        if($notification) {
            $notification->send($workOrderHistory);
        }
    }

}
