<?php namespace App\Services;

use App\Enums\AccessTypeEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService {

    public static function SendErrorMail($e, $accessType = AccessTypeEnum::Web){

        $request = request();
        $url = strtolower($request->url());
        $method = $request->method();

        if(strpos($url, '/api/') !== false){
            $accessType = AccessTypeEnum::Api;
        }

        /*
        if($accessType == AccessTypeEnum::Api){
            // Add log
            Log::useDailyFiles(Config::get('app.path_log_api'));
            Log::error($e);
        }
        */

        $date = Carbon::now()->format('d/m/Y h:i:s a');

        // Setup your smtp mailer
        $transport = new \Swift_SmtpTransport(
            Config::get('app.mail_error_host'),
            Config::get('app.mail_error_port'),
            Config::get('app.mail_error_encryption')
        );
        $transport->setUsername(Config::get('app.mail_error_username'));
        $transport->setPassword(Config::get('app.mail_error_password'));


        // Any other mailer configuration stuff needed...
        $smtp = new \Swift_Mailer($transport);

        // Set the mailer as smtp
        Mail::setSwiftMailer($smtp);

        // Send message
        try{
            Mail::send('templates.emails.error', compact('e', 'date', 'url', 'method'), function ($message) {
                $message->from(Config::get('app.mail_error_from'), Config::get('app.mail_error_from_head'));
                $message->to(Config::get('app.mail_error_to'))->subject(Config::get('app.mail_error_subject'));
            });
        } catch (\Exception $e) {
            Log::error($e);
        }

    }

    public static function SendMainMail($view_path, $data, $from, $from_head, $to, $cc, $bcc, $subject)
    {
        $cc = $cc ? (count($cc) == 0 ? null : $cc) : null;
        $bcc = $bcc ? (count($bcc) == 0 ? null : $bcc) : null;

        // Setup your smtp mailer
        $transport = new \Swift_SmtpTransport(
            \Config::get('app.mail_main_host'),
            \Config::get('app.mail_main_port'),
            \Config::get('app.mail_main_encryption')
        );
        $transport->setUsername(\Config::get('app.mail_main_username'));
        $transport->setPassword(\Config::get('app.mail_main_password'));

        // Any other mailer configuration stuff needed...
        $smtp = new \Swift_Mailer($transport);

        // Set the mailer as smtp
        \Mail::setSwiftMailer($smtp);

        // Send message
        \Mail::send($view_path, $data, function ($message) use ($from, $from_head, $to, $cc, $bcc, $subject) {

            $message->from($from, $from_head);
            $message->to($to);
            $message->subject($subject);

            if($cc)
                $message->cc($cc);

            if($bcc)
                $message->bcc($bcc);

        });

    }

}
