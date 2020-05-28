<?php

namespace App\UseCases\Ticket\NotifyEmail;

use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\Views\TicketSearch;
use App\Services\MailService;

class NotifyEmailByNewStatus
{
    public function execute(int $ticketId, $user)
    {
        try {
            $ticketViewData = TicketSearch::find($ticketId);
            $emails = $this->getEmails();

            $dataForTemplate = [
                'data' => $ticketViewData
            ];

            $this->send($emails, $ticketViewData, $dataForTemplate, $user);
        } catch (\Exception $e) {
            MailService::SendErrorMail($e);
        }
    }

    private function send($emails, $ticketViewData, $dataForTemplate, $user): void
    {
        if (count($emails) > 0) {

            $cc = array();
            array_push($cc, $user->email);

            $to = $emails;
            $subject = str_replace(':ticket_number', $ticketViewData->ticket_number, \Config::get('app.ticket_email_new_subject'));
            $pathTemplate = 'templates.emails.ticket.new';
            $mailFrom = \Config::get('app.mail_from');
            $mailFromHead = \Config::get('app.ticket_email_new_from_head');

            MailService::SendMainMail($pathTemplate, $dataForTemplate, $mailFrom, $mailFromHead, $to, $cc, null, $subject);
        }
    }

    private function getEmails()
    {
        # All gestor Infraestructura users
        $usersGestorInfraestructuraTypeEmail = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::GESTOR_INFRAESTRUCTURA_ID);
        $usersGestorInfraestructuraTypeEmail = array_map(function ($value) {
            return $value->email;
        }, $usersGestorInfraestructuraTypeEmail);

        $emails = array_unique($usersGestorInfraestructuraTypeEmail);

        return $emails;
    }
}
