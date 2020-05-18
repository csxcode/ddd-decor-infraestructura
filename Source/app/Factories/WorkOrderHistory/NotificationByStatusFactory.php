<?php

namespace App\Factories\WorkOrderHistory;

use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderStatus;
use App\UseCases\WorkOrderHistory\NotifyEmailByClosed;
use App\UseCases\WorkOrderHistory\NotifyEmailByConfirmed;
use App\UseCases\WorkOrderHistory\NotifyEmailByFinished;
use App\UseCases\WorkOrderHistory\NotifyEmailByInconforme;
use App\UseCases\WorkOrderHistory\NotifyEmailByPaused;
use App\UseCases\WorkOrderHistory\NotifyEmailByReopening;

class NotificationByStatusFactory
{
    public function initialize($status, WorkOrder $oldWorkOrder)
    {
        switch ($status)
        {
            case WorkOrderStatus::STATUS_CERRADO:
                return new NotifyEmailByClosed();
                break;

            case WorkOrderStatus::STATUS_CONFIRMADO:
                return new NotifyEmailByConfirmed();
                break;

            case WorkOrderStatus::STATUS_TERMINADO:
                return new NotifyEmailByFinished();
                break;

            case WorkOrderStatus::STATUS_PAUSADO:
                return new NotifyEmailByPaused();
                break;

            case WorkOrderStatus::STATUS_REAPERTURADO:
                return new NotifyEmailByReopening();
                break;

            case WorkOrderStatus::STATUS_INICIADO:

                if($oldWorkOrder->work_order_status_id == WorkOrderStatus::STATUS_TERMINADO)
                    return new NotifyEmailByInconforme();
                break;

            default:
                return null;
                break;
        }
    }
}
