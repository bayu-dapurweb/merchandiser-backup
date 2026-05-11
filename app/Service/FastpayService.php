<?php 
namespace App\Service;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FastpayService
{
    public static function expressPay(
        $order_code,
        $grand_total,
        $order_at,
        $expired_at,
        $cust_id,
        $cust_name,
        $user_phone,
        $user_email
    ) {
        if (empty($user_phone)) {
            $user_phone = "6289671304121";
        }
        $cust_name = slug($cust_name);
        $cust_name = str_replace("-", " ", $cust_name);
        $cust_name = strtoupper($cust_name);
        $curl = curl_init();
        $body ='{
            "merchant_id":"'.env('FASTPAY_MERCHANT_ID').'", 
            "bill_no":"'.$order_code.'", 
            "bill_date":"'.$order_at.'",
            "bill_expired":"'.$expired_at.'", 
            "bill_total":"'.$grand_total.'", 
            "bill_desc":"Pembayaran #'.$order_code.'", 
            "cust_no":"'.$cust_id.'",
            "cust_name":"'.$cust_name.'", 
            "msisdn":"'.$user_phone.'",
            "email":"'.$user_email.'", 
            "return_url":"'.env('FASTPAY_CALLBACK').'", 
            "item":[
                {
                    "product":"Evista Inv.'.date("Y-m").'/'.$order_code.'", 
                    "qty":"1",
                    "amount":"'.$grand_total.'"
                }
            ],
            "button_color":"2e0264", 
            "background_color":"ffffff",
            "signature":"'.signature($order_code, $grand_total).'"
        }';
        

        curl_setopt_array($curl, array(
        CURLOPT_URL => env('FASPAY_XPRESS_URL','https://xpress-sandbox.faspay.co.id/v4/post'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        Log::debug("fastpay-checkout-request:$order_code:" . $body);
        Log::debug("fastpay-checkout-response:$order_code:" . $response);
        
        return json_decode($response, true);

    }


    public static function paymentchannel()
    {
        $curl = curl_init();

        $postfield = json_encode([
            "request"       => "Request List of Payment Gateway",
            "merchant_id"   => env("FASTPAY_MERCHANT_ID"),
            "merchant"      => "EVISTA",
            "signature"     => signature()
        ]);

        curl_setopt_array($curl, array(
        CURLOPT_URL => env("FASPAY_CHANNEL_INQUIRY", "https://debit-sandbox.faspay.co.id/cvr/100001/10"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfield,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        $res = json_decode($response, true);
        
        return $res;

    }

    public static function fastpaycustom(
        $order_code,
        $grand_total,
        $order_at,
        $expired_at,
        $cust_id,
        $cust_name,
        $user_phone,
        $user_email,
        $payment_channel
    ) {
        $cust_name = slug($cust_name);
        $cust_name = str_replace("-", " ", $cust_name);
        $cust_name = strtoupper($cust_name);
        $curl = curl_init();
        $grand_total = $grand_total * 100;
        $postdata = '{
            "request":"Post Data Transaction",
            "merchant_id":"'.env('FASTPAY_MERCHANT_ID').'", 
            "merchant":"EVISTA",
            "bill_no":"'.$order_code.'", 
            "bill_date":"'.$order_at.'",
            "bill_expired":"'.$expired_at.'", 
            "bill_total":"'.$grand_total.'", 
            "bill_desc":"Pembayaran #'.$order_code.'", 
            "cust_no":"'.$cust_id.'",
            "cust_name":"'.$cust_name.'", 
            "msisdn":"'.$user_phone.'",
            "email":"'.$user_email.'", 
            "terminal":"10",
            "billing_name":"0",
            "payment_channel":"'.$payment_channel.'",
            "receiver_name_for_shipping":"'.$cust_name.'",
            "pay_type":"1",
            "item":[
                {
                    "product":"Evista Inv.'.date("Y-m").'/'.$order_code.'", 
                    "qty":"1",
                    "amount":"'.$grand_total.'",
                    "tenor":"00",
                    "payment_plan":"01"
                }
            ],
            "reserve1":"",
            "reserve2":"",  
            "signature":"'.signature($order_code, "").'"
            }';

        curl_setopt_array($curl, array(
        CURLOPT_URL => env('FASPAY_CUSTOM_PAYMENT_URL','https://debit-sandbox.faspay.co.id/cvr/300011/10'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postdata,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }
        curl_close($curl);

        Log::debug("fastpay-custom-checkout-request:$order_code:" . $postdata);
        Log::debug("fastpay-custom-checkout-response:$order_code:" . $response);
        
        return json_decode($response, true);
    }
}