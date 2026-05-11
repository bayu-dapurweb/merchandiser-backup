<?php 
namespace App\Service;

use App\Districts;

class SAPService
{
    public function login()
    {

        $url = env('SAP_SERVER') . '/b1s/v1/Login';
        $data = [
            'CompanyDB' => env("SAP_DB"),
            'Password' => env("SAP_PASS"),
            'UserName' => env("SAP_USER")
        ];

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR      => __DIR__ . '/../../storage/app/uploads/cookie/lecookie.txt',
            CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification (insecure, use with caution)
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data)
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        if (env('SAP_SYNC_ENABLE', true)) {
            $response = curl_exec($ch);
        }
        
        curl_close($ch);

        $log = new \App\LogSapApiCall;
        $log->target_url = $url;
        $log->related_table = "login";
        $log->related_id = '';
        $log->request_body = json_encode($data);
        $log->response_body = json_encode($response);
        $log->save();

    }

    public function apiGetCall($url)
    {
        /* call after login */
        $cookieFile = __DIR__ . '/../../storage/app/uploads/cookie/lecookie.txt';
        $error = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if (env('SAP_SYNC_ENABLE', true)) {
            $response = curl_exec($ch);
        }

        if (curl_errno($ch)) {
            // echo 'Curl error: ' . curl_error($ch);
            $error = json_decode($response, true);
        }

        curl_close($ch);

        if ($error !== false) {
            return json_decode($error, true);
        } else {
            return json_decode($response, true);
        }
        
    }

    public function apiPostCall($url, $data, $custom_method = 'POST')
    {
        /* call after login */
        $cookieFile = __DIR__ . '/../../storage/app/uploads/cookie/lecookie.txt';
        $data_stringfy = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($custom_method != 'POST') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'B1S-ReplaceCollectionsOnPatch: true']
            );
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/plain',
                'Expect:'
            ]);
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_stringfy);
        if (env('SAP_SYNC_ENABLE', true)) {
            $response = curl_exec($ch);
        }

        return json_decode($response, true);
    }

    public static function post($url, $bodyjson, $with_status = false)
    {
        if (env('SAP_LIVE', false)) {
            $url .= 'Live';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyjson);
        
        
        if ($with_status) {
            $response = null;
            if (env('SYNC_ENABLE', true)) {
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    $error = curl_error($ch);
                }
                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            }

            curl_close($ch);
            if ($error) {
                return [
                    'data' => $error,
                    'code' => 500
                ];
            }
            return [
                'data' => json_decode($response, true),
                'code' => $httpStatusCode
            ];
        } else {
            if (env('SAP_SYNC_ENABLE', true)) {
                $response = curl_exec($ch);
            }
            return json_decode($response, true);
        }
    }
}