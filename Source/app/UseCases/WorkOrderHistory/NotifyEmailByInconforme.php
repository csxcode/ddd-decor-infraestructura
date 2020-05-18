<?php
namespace App\UseCases\WorkOrderHistory;

use App\Contracts\INotifyByStatus;
use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\Vendor;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrderHistory;
use App\Models\WorkOrder\WorkOrderQuote;
use App\Models\WorkOrder\WorkOrderQuoteStatus;
use App\Services\MailService;

class NotifyEmailByInconforme implements INotifyByStatus
{
    public function send(WorkOrderHistory $workOrderHistory)
    {
        try {

            # Get some data
            # ------------------------------------------------------
            $workOrderSearch = WorkOrderSearch::find($workOrderHistory->work_order_id);

            $workOrderQuote = WorkOrderQuote::where('work_order_id', $workOrderHistory->work_order_id)
                ->where('quote_status_id', WorkOrderQuoteStatus::STATUS_ACEPTADO)
                ->first();

            $dataForTemplate = [
                'wo' => $workOrderSearch,
                'woh' => $workOrderHistory
            ];

            # Get Emails
            # ------------------------------------------------------

            # Vendor
            $vendorEmail = array();

            if($workOrderQuote){
                $vendor = Vendor::find($workOrderQuote->vendor_id);
                $vendorEmail = [$vendor->email];
            }

            # All gestor Infraestructura users
            $usersGestorInfraestructuraTypeEmail = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::GESTOR_INFRAESTRUCTURA_ID);
            $usersGestorInfraestructuraTypeEmail = array_map(function ($value) {
                return $value->email;
            }, $usersGestorInfraestructuraTypeEmail);

            $emails = array_merge($vendorEmail, $usersGestorInfraestructuraTypeEmail);

            # All responsable sede users related
            $usersSedeTypeRelatedEmail = UserStoreBranch::GetUsersEmailByRoleAndBranchRelated(UserRoleEnum::RESPONSABLE_SEDE_ID, $workOrderSearch->branch_id);
            $usersSedeTypeRelatedEmail = array_map(function ($value) {
                return $value->email;
            }, $usersSedeTypeRelatedEmail);

            # Mergue and remove duplicates emails
            $emails = array_merge($emails, $usersSedeTypeRelatedEmail);
            $emails = array_unique($emails);

            # Send Email
            # ------------------------------------------------------
            if (count($emails) > 0)
            {
                $to = $emails;
                $subject = str_replace(':number', $workOrderSearch->wo_number, \Config::get('app.wo_email_inconforme_subject'));
                $pathTemplate = 'templates.emails.wo.inconforme';
                $mailFrom = \Config::get('app.mail_from');
                $mailFromHead = \Config::get('app.wo_email_inconforme_from_head');

                MailService::SendMainMail($pathTemplate, $dataForTemplate, $mailFrom, $mailFromHead, $to, null, null, $subject);
            }

        } catch (\Exception $e) {
            MailService::SendErrorMail($e);
        }
    }

}
