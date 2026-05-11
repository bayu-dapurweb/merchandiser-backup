<?php 
namespace App\Service;

use App\Districts;

class FirebaseService
{
    public static function fcmsend(Array $recipients, $title, $message, $order_id = null, $type = null)
    {
        // $status = fcm()
        //     ->to($recipients)
        //     ->priority('high')
        //     ->timeToLive(0)
        //     ->data([
        //         'title' => $title,
        //         'body' => $message,
        //     ])
        //     ->send();

        // return $status;
        // $order = '';
        // if ($order_id !== null) {
        //     $order = ',"data": {
        //         "order_id": "'.$order_id.'"
        //     }';
        // }
        // $postfield = '{
        //     "registration_ids":'.json_encode($recipients).',
        //     "notification": {
        //         "title": "'.$title.'",
        //         "body": "'.$message.'"
        //     }'.$order.'
        // }';

        $post = [
            "registration_ids" => $recipients,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ],
            "data" => [
                "order_id" => $order_id,
                "type" => $type,
            ]
        ];
        $postfield = json_encode($post);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfield,
        CURLOPT_HTTPHEADER => array(
            'Authorization: key=' .env("FCM_SERVER_KEY"),
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        $json = json_decode($response, true);

        return $json;

    }

    public static function send($to, $title, $body)
    {
        $tokenobj = \App\UserToken::where("firebase_token", $to)->orderBy("id", "desc")->first();
        if (!empty($tokenobj)) {
            $user = \App\User::find($tokenobj->user_id);

            $notification = new \App\Notification;
            $notification->classes_id = 0;
            $notification->users_id = $user->id;
            $notification->icon = uri("image/notification/notificaiton-payment-alert.png?202211270136");
            $notification->notification_type = "user";
            $notification->is_read = 0;
            $notification->title = $title;
            $notification->body = $body;
            $notification->save();

            $notification_user = new \App\UserNotification;
            $notification_user->users_id = $user->id;
            $notification_user->notifications_id = $notification->id;
            $notification_user->save();
        }
        
    
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "to":"'.$to.'",
        "notification": {
            "title": "'.$title.'",
            "body": "'.$body.'"
        }
        }',
        CURLOPT_HTTPHEADER => array(
            'Authorization: key=' .env("FCM_SERVER_KEY"),
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        $json = json_decode($response, true);

        return $json;

    }

    public static function sendBulk($registration_ids, $title, $body)
    {
        $postfield = '{
            "registration_ids":'.json_encode($registration_ids).',
            "notification": {
                "title": "'.$title.'",
                "body": "'.$body.'"
            }
        }';

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfield,
        CURLOPT_HTTPHEADER => array(
            'Authorization: key=' .env("FCM_SERVER_KEY"),
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        $json = json_decode($response, true);

        return $json;

    }
}