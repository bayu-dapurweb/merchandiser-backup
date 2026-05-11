<?php 
namespace App\Service;

use App\TrxNotifications;

class NotificationService
{
    public static function create($title, $message, $type, $user_id, $ref_order_id = 0, $send_fcm_to = 0, $send_email_to = 0)
    {
        $notification = new TrxNotifications;
        $notification->title = $title;
        $notification->message = $message;
        $notification->ref_users_id = $user_id;
        $notification->is_read = 0;
        $notification->is_send_fcm = 0;
        $notification->notif_type = $type;
        $notification->ref_orders_id = $ref_order_id;
        $notification->save();

        if ($send_fcm_to == 1) {
            NotificationService::sendFCM($notification);
        }

        if ($send_email_to == 1) {
            NotificationService::sendEmail($notification);
        }
    }

    public static function sendEmail(\App\TrxNotifications $notification)
    {
        
        $user = \App\RefUsers::find($notification->ref_users_id);
        
        $name = $user->fullname;
        // dd($user->gmail);
        $email = decrypt_string($user->gmail);
        $subject = $notification->title;
        $message = $notification->message;
        
        if (!empty($email)) {
            $mail = \App\Service\MailService::send([
                'name'      => $name,
                'email'     => $email,
                'subject'   => $subject,
                'message'   => "Dear " . $name . ", <br>" . $message . "<br><br>" . "Evista Management",
            ]);
        } else {
            \Log::info('#INFO : Notification #' . $notification->id . ", have invalid email, the message will not send");
        }
        

        
    }

    public static function sendFCM(\App\TrxNotifications $notification)
    {
        $token = \App\UserToken::where("user_id", $notification->ref_users_id)
        ->orderBy("id", "desc")
        ->first();

        if (!empty($fcm_token) && strlen($fcm_token) > 100)  {
            $status = \App\Service\FirebaseService::fcmsend([$token->firebase_token], $notification->title, $notification->message, $notification->ref_orders_id, $notification->notif_type);   
        }

        $notification->is_send_fcm = 1;
        $notification->save();
    }
}