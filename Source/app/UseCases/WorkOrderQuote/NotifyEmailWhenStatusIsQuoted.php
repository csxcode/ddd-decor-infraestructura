<?php
namespace App\UseCases\WorkOrderQuote;

use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\Vendor;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Services\MailService;

class NotifyEmailWhenStatusIsQuoted
{
    public function execute(WorkOrderQuote $workOrderQuote)
    {
        try {

            # Get some data
            # ------------------------------------------------------
            $workOrderSearch = WorkOrderSearch::find($workOrderQuote->work_order_id);
            $vendor = Vendor::find($workOrderQuote->vendor_id);

            $dataForTemplate = [
                'wo' => $workOrderSearch,
                'quote' => $workOrderQuote,
                'vendor' => $vendor
            ];

            # Get Emails
            # ------------------------------------------------------

            # All gestor Infraestructura users
            $usersGestorInfraestructuraTypeEmail = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::GESTOR_INFRAESTRUCTURA_ID);
            $usersGestorInfraestructuraTypeEmail = array_map(function ($value) {
                return $value->email;
            }, $usersGestorInfraestructuraTypeEmail);


            $emails = $usersGestorInfraestructuraTypeEmail;

            # Send Email
            # ------------------------------------------------------
            if (count($emails) > 0)
            {
                $to = $emails;
                $subject = str_replace(':vendor_name', $vendor->name, \Config::get('app.wo_quote_email_submit_subject'));
                $subject = str_replace(':wo_number', $workOrderSearch->wo_number, $subject);
                $pathTemplate = 'templates.emails.wo_quote.submit';
                $mailFrom = \Config::get('app.mail_from');
                $mailFromHead = \Config::get('app.wo_quote_email_submit_from_head');

                MailService::SendMainMail($pathTemplate, $dataForTemplate, $mailFrom, $mailFromHead, $to, null, null, $subject);
            }

        } catch (\Exception $e) {
            MailService::SendErrorMail($e);
        }
    }
}
