<?php

namespace App\Service;

class IsellerTokenService
{
    public static function refresh()
    {
        $initial_refresh_token = env("ISELLER_REFRESH_TOKEN");
        $last_refresh_token = \App\LogIsellerTokens::select("refresh_token")->orderBy("id", "desc")->first();
        if (!empty($last_refresh_token) && !empty($last_refresh_token->refresh_token)) {
            $initial_refresh_token = $last_refresh_token->refresh_token;
        }
        
        $reqjson = json_encode([
            'grant_type' => 'refresh_token',
            'refresh_token' => $initial_refresh_token,
            'client_id' => env('ISELLER_CLIENT_ID'),
            'client_secret' => env('ISELLER_CLIENT_SECRET')
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://isellershop.com/oauth/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => ($reqjson),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        $json = json_decode($response, true);

        if (!empty($json['access_token']) && !empty($json['refresh_token'])) {

            /* save to token log */
            $log = new \App\LogIsellerTokens;
            $log->access_token = $json['access_token'];
            $log->token_type = $json['token_type'];
            $log->expires_in = $json['expires_in'];
            $log->refresh_token = $json['refresh_token'];
            $log->resource_url = $json['resource_url'];
            $log->status = 'success';
            $log->initial_refresh_token = $initial_refresh_token;
            $log->save();

            cache_set('ISELLER_ACCESS_TOKEN', $json['access_token']);

        } else {

            /* save as invalid */
            $log = new \App\LogIsellerTokens;
            $log->meta_res = $response;
            $log->status = 'error';
            $log->initial_refresh_token = $initial_refresh_token;
            $log->save();
        }

    }
}