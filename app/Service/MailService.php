<?php 
namespace App\Service;

use App\Districts;

class MailService
{
    public static function contact($param)
    {
        \Mail::send('emails.general', ['param' => $param], function ($m) use ($param) {
            $m->from(env("MAIL_USERNAME", "info@evista.id"), 'EVISTA');
            $m->to($param["email"], $param["name"])->subject('Hi! '.$param["name"].', thanks for contact us');
        });
    }

    public static function otp($param)
    {
        $param['tipe'] = "otp";
        $mailerqueue = new \App\TrxMailerQueue;
        $mailerqueue->param = json_encode($param);
        $mailerqueue->mail_to = $param["email"];
        $mailerqueue->mail_to_name = $param["name"];
        $mailerqueue->subject = $param["subject"];
        $mailerqueue->save();
    }

    public static function send($param)
    {
        $param['tipe'] = "send";
        $mailerqueue = new \App\TrxMailerQueue;
        $mailerqueue->param = json_encode($param);
        $mailerqueue->mail_to = $param["email"];
        $mailerqueue->mail_to_name = $param["name"];
        $mailerqueue->subject = $param["subject"];
        $mailerqueue->save();
    }

    public static function sendfromcron()
    {
        $ques = \App\TrxMailerQueue::where([
            ["is_send" , 0],
            ["try", "<=", 2]
        ])->get();

        foreach ($ques as $mail) {
            $is_error = null;
            try {
                $param = json_decode($mail['param'], true);
                if ($param['tipe'] == "send") {
                    \Mail::send('emails.general', ['param' => $param], function ($m) use ($param) {
                        $m->from(env("MAIL_USERNAME", "info@evista.id"), ' EVISTA');
                        $m->to($param["email"], $param["name"])->subject($param['subject']);
                    });
                }
                if ($param['tipe'] == "otp") {
                    \Mail::send('emails.otp', ['param' => $param], function ($m) use ($param) {
                        $m->from(env("MAIL_USERNAME", "info@evista.id"), ' EVISTA');
                        $m->to($param["email"], $param["name"])->subject($param['subject']);
                    });
                }

                $mail->is_send = 1;
                $mail->save();
            } catch(Exception $e) {
                $mail->exception = json_encode($e);
                $mail->try = $mail->try + 1;
                $mail->save();
            }
        }
    }
}