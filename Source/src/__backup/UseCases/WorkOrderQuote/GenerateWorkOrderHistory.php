<?php
namespace App\UseCases\WorkOrderQuote;


use App\Models\Vendor;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Models\WorkOrder\WorkOrderQuoteStatus;
use App\Models\WorkOrder\WorkOrderStatus;
use App\Services\WorkOrderHistoryService;
use App\Services\WorkOrderService;

class GenerateWorkOrderHistory
{
    public function run(WorkOrderQuote $oldData, WorkOrderQuote $data, $user) : bool
    {
        $return = true;

        // Si el estado de la cotización cambia de Pendiente (1) a Aceptado (3)
        $hasChanged = $oldData->quote_status_id == WorkOrderQuoteStatus::STATUS_PENDIENTE &&
            $data->quote_status_id == WorkOrderQuoteStatus::STATUS_ACEPTADO;

        if(!$hasChanged)
            return false;

        // generate history
        $vendor = Vendor::findOrFail($data->vendor_id);

        $workOrderHistoryService = new WorkOrderHistoryService(new WorkOrderSearch(), new WorkOrderService(new WorkOrder()));

        $save = [
            'work_order_id' => $data->work_order_id,
            'work_order_status_id' => WorkOrderStatus::STATUS_PENDIENTE_INICIAR,
            'work_report' => 'El proveedor ' . $vendor->name . ' fue asignado para ejecutar la orden de trabajo',
        ];

        $workOrderHistoryService->store($save, $user);

        return $return;
    }

}
