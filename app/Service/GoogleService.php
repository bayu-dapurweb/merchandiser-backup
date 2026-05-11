<?php 

namespace App\Service;

class GoogleService
{
    public static function GoogleIdValidate($google_id, $token)
    {
        // $client = new \Google_Client();
        // $client->setClientId(env('GOOGLE_CLIENT_ID'));
        // $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        // $client->setAuthConfig(__DIR__ . "/../../" . env('GOOGLE_CLIENT_JSON'));
        // $client->setIdToken($google_id
        // $client->addScope("openid");
        // $client->addScope("email");
        // $client->setAccessToken($token);
        // $payload = $client->verifyIdToken($token);
        // dd($payload);

        // $client->setScopes(['https://www.googleapis.com/auth/userinfo.profile']);
        // $client->setAccessToken($token); // Replace with the user's access token
        // $oauth2Service = new \Google_Service_Oauth2($client);
        // $userInfo = $oauth2Service->userinfo->get();
        // dd($userInfo);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response, true);

        if (!empty($data['error'])) {
            $error = [$data['error']];
        } else {
            if ($google_id != $data['sub']) {
                $error = ["Google ID and Token not match"];
            }
        }        

        return [
            'data' => $data ?? [],
            'error' => $error ?? []
        ];
    }
}