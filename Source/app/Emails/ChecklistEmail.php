<?php namespace App\Emails;

use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\Views\CheckListSearch;
use App\Services\MailService;
use Illuminate\Support\Facades\Config;

class ChecklistEmail {
    
    /**
     * Notificación email cuando el registro checklist es terminado de editar (completed)
     * 
     */
    public static function NotifyWhenChecklistWasCompleted($checklist_id){

        $data = CheckListSearch::where('id', $checklist_id)->first();

        $emails = UserStoreBranch::GetUsersEmailByRoleAndBranchRelated(UserRoleEnum::STORE_MANAGER_ID, $data->branch_id);

        if(count($emails)>0){

            $to = Array();

            // get emails (to)
            foreach ($emails as $item){
                array_push($to, $item->email);
            }

            // get data
            $cc = null;
            $bcc = null;           
            $subject = str_replace(':number', $data->checklist_number, Config::get('app.mail_completed_checklist_subject'));

            // send email
            MailService::SendMainMail(
                'templates.emails.checklist.completed',
                compact('data'),
                Config::get('app.mail_from'),
                Config::get('app.mail_completed_checklist_from_head'),
                $to,
                $cc,
                $bcc,
                $subject
            );

        }
    }

     /**
     * Notificación email cuando el checklist ha sido aprobado o rechazado
     * 
     */
    public static function NotifyWhenChecklistWasApprovedOrRejected($checklist_id){

        $data = CheckListSearch::where('id', $checklist_id)->first();        
        $emails = UserStoreBranch::GetUsersEmailByRole(UserRoleEnum::VISUAL_ID);        

        if(count($emails)>0){

            $to = Array();

            // get emails (to)
            foreach ($emails as $item){
                array_push($to, $item->email);
            }

            // get data
            $cc = null;
            $bcc = null;      
            $toHead = str_replace(':status', $data->status_name, Config::get('app.mail_appro_rejec_checklist_from_head'));            
            $subject = str_replace(':number', $data->checklist_number, Config::get('app.mail_appro_rejec_checklist_subject'));
            $subject = str_replace(':status', $data->status_name, $subject);

            // send email
            MailService::SendMainMail(
                'templates.emails.checklist.approved_rejected',
                compact('data'),
                Config::get('app.mail_from'),
                $toHead,
                $to,
                $cc,
                $bcc,
                $subject
            );

        }
    }
   
}