<?php
namespace App\UseCases\WorkOrderQuote;

use App\Enums\QuoteNotificationEnum;
use App\Models\Vendor;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Services\MailService;
use App\Services\WorkOrderContactService;

class NotifyQuoteInvitationByEmail
{
    public function run(WorkOrderQuote $oldData, WorkOrderQuote $data) : bool
    {
        $wasNotified = false;

        // Check if has changed
        $woqNotificationHasChanged = $oldData->notification != $data->notification;

        if (!$woqNotificationHasChanged)
            return false;

        // Si el valor del campo notification cambia de cualquier valor (0 = No requiere notificación o 2 = Notificación Enviada) hacía 1 (Pendiente notificar)
        $oldNotificationWasDifferentFromPending = $oldData->notification != QuoteNotificationEnum::Pendiente;

        if (!$oldNotificationWasDifferentFromPending)
            return false;

        // Check if woq was updated as pendiente
        $woqNotificationWillBe_Pending = $data->notification == QuoteNotificationEnum::Pendiente;

        if (!$woqNotificationWillBe_Pending)
            return false;

        // Check vendor
        $vendor = Vendor::find($data->vendor_id);

        if (!$vendor)
            return false;

        // Check email is valid
        if(!\FunctionHelper::isValidEmail($vendor->email)) {
            \Log::error('Email: '.$vendor->email.' does not valid to send notification of invitation');
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
                'contacts' => $workOrderContacts
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

        $subject = str_replace(':number', $workOrderData->wo_number, \Config::get('app.mail_invitation_woq_subject'));
        $pathTemplate = 'templates.emails.wo_quote.invitation';
        $mailFrom = \Config::get('app.mail_from');
        $mailFromHead = \Config::get('app.mail_invitation_woq_from_head');

        MailService::SendMainMail($pathTemplate, $data, $mailFrom, $mailFromHead, $to, $cc, $bcc, $subject);
    }

}
