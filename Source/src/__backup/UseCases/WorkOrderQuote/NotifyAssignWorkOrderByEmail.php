<?php
namespace App\UseCases\WorkOrderQuote;

use App\Models\Vendor;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Models\WorkOrder\WorkOrderQuoteStatus;
use App\Services\MailService;
use App\Services\WorkOrderContactService;

class NotifyAssignWorkOrderByEmail
{
    public function run(WorkOrderQuote $oldData, WorkOrderQuote $data) : bool
    {
        $wasNotified = false;

        // Si el estado de la cotización cambia de Pendiente (1) a Aceptado (3)
        $hasChanged = $oldData->quote_status_id == WorkOrderQuoteStatus::STATUS_PENDIENTE &&
            $data->quote_status_id == WorkOrderQuoteStatus::STATUS_ACEPTADO;

        if(!$hasChanged)
            return false;

        // Check email is valid
        $vendor = Vendor::findOrFail($data->vendor_id);

        if(!\FunctionHelper::isValidEmail($vendor->email)) {
            \Log::error('Email: '.$vendor->email.' does not valid to send notification of assignment');
            return false;
        }

        // Send notification
        try {
            $to = [$vendor->email];
            $workOrderSearch = WorkOrderSearch::find($data->work_order_id);
            $workOrderContactService = new WorkOrderContactService(new WorkOrderSearch());
            $workOrderContacts = $workOrderContactService->getContactUsersByWorkOrder($data->work_order_id);


            $dataForTemplate = [
                'data' => $workOrderSearch,
                'contacts' => $workOrderContacts,
                'quote' => $data
            ];

            $this->sendNotification($to, null, null, $dataForTemplate);
            $wasNotified = true;

        } catch (\Exception $e) {
            \Log::error($e);
        }

        return $wasNotified;
    }

    private function sendNotification($to, $cc, $bcc, $data) : void
    {
        $workOrderData = $data['data'];

        $subject = str_replace(':number', $workOrderData->wo_number, \Config::get('app.mail_assignment_woq_subject'));
        $pathTemplate = 'templates.emails.wo_quote.assign_work_order';
        $mailFrom = \Config::get('app.mail_from');
        $mailFromHead = \Config::get('app.mail_assignment_woq_from_head');

        MailService::SendMainMail($pathTemplate, $data, $mailFrom, $mailFromHead, $to, $cc, $bcc, $subject);
    }

}
