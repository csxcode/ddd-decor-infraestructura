<?php
namespace App\UseCases\WorkOrderHistory;

use App\Contracts\INotifyByStatus;
use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\Views\WorkOrderSearch;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderHistory;
use App\Services\MailService;

class NotifyEmailByFinished implements INotifyByStatus
{
    public function send(WorkOrderHistory $workOrderHistory)
    {
        try {

            # Get some data
            # ------------------------------------------------------
            $workOrderSearch = WorkOrderSearch::find($workOrderHistory->work_order_id);

            $dataForTemplate = [
                'wo' => $workOrderSearch,
                'woh' => $workOrderHistory
            ];

            # Get Emails
            # ------------------------------------------------------

            # All gestor infraestructura users
            $usersGestorTypeEmail = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::GESTOR_INFRAESTRUCTURA_ID);
            $usersGestorTypeEmail = array_map(function ($value) {
                return $value->email;
            }, $usersGestorTypeEmail);

            # All responsable sede users related
            $usersSedeTypeRelatedEmail = UserStoreBranch::GetUsersEmailByRoleAndBranchRelated(UserRoleEnum::RESPONSABLE_SEDE_ID, $workOrderSearch->branch_id);
            $usersSedeTypeRelatedEmail = array_map(function ($value) {
                return $value->email;
            }, $usersSedeTypeRelatedEmail);

            # Mergue and remove duplicates emails
            $emails = array_merge($usersGestorTypeEmail, $usersSedeTypeRelatedEmail);
            $emails = array_unique($emails);

            # Send Email
            # ------------------------------------------------------
            if (count($emails) > 0)
            {
                $to = $emails;
                $subject = str_replace(':number', $workOrderSearch->wo_number, \Config::get('app.wo_email_terminado_subject'));
                $pathTemplate = 'templates.emails.wo.terminado';
                $mailFrom = \Config::get('app.mail_from');
                $mailFromHead = \Config::get('app.wo_email_terminado_from_head');

                MailService::SendMainMail($pathTemplate, $dataForTemplate, $mailFrom, $mailFromHead, $to, null, null, $subject);
            }

        } catch (\Exception $e) {
            MailService::SendErrorMail($e);
        }
    }
}
