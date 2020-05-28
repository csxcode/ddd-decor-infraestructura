<?php

namespace App\UseCases\Ticket\NotifyEmail;

use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\Views\TicketSearch;
use App\Models\WorkOrder\WorkOrder;
use App\Services\MailService;

class NotifyEmailByConfirmedStatus
{
    public function execute(int $ticketId, WorkOrder $workOrder)
    {
        try {
            $ticketViewData = TicketSearch::find($ticketId);
            $emails = $this->getEmails($ticketViewData);

            $dataForTemplate = [
                'ticket' => $ticketViewData,
                'wo_number' => $workOrder->wo_number
            ];

            $this->send($emails, $ticketViewData, $dataForTemplate);
        } catch (\Exception $e) {
            MailService::SendErrorMail($e);
        }
    }

    private function send($emails, $ticketViewData, $dataForTemplate): void
    {
        if (count($emails) > 0) {
            $to = $emails;
            $subject = str_replace(':ticket_number', $ticketViewData->ticket_number, \Config::get('app.ticket_email_confirmed_subject'));
            $pathTemplate = 'templates.emails.ticket.confirmed';
            $mailFrom = \Config::get('app.mail_from');
            $mailFromHead = \Config::get('app.ticket_email_confirmed_from_head');

            MailService::SendMainMail($pathTemplate, $dataForTemplate, $mailFrom, $mailFromHead, $to, null, null, $subject);
        }
    }

    private function getEmails($ticketViewData)
    {
        # All gestor Infraestructura users
        $usersGestorInfraestructuraTypeEmail = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::GESTOR_INFRAESTRUCTURA_ID);
        $usersGestorInfraestructuraTypeEmail = array_map(function ($value) {
            return $value->email;
        }, $usersGestorInfraestructuraTypeEmail);

        # All responsable sede users related
        $usersSedeTypeRelatedEmail = UserStoreBranch::GetUsersEmailByRoleAndBranchRelated(UserRoleEnum::RESPONSABLE_SEDE_ID, $ticketViewData->branch_id);
        $usersSedeTypeRelatedEmail = array_map(function ($value) {
            return $value->email;
        }, $usersSedeTypeRelatedEmail);

        # Mergue and remove duplicates emails
        $emails = array_merge($usersGestorInfraestructuraTypeEmail, $usersSedeTypeRelatedEmail);
        $emails = array_unique($emails);

        return $emails;
    }
}
