<?php 
namespace App\Service;

class FlipService
{
    //create payment transfer manual
    public static function createPayment($order_id, $payment_method_id, $amount)
    {
        $order = \App\TrxOrders::find($order_id);
        $ordercode = $order->ordercode;
        
        $payment_method = \App\RefPaymentMethod::find($payment_method_id);
        $post = array('account_number' => $payment_method->account_number,'bank_code' => $payment_method->bank,'amount' => $amount);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env("FLIP_V3_BASE_URL", 'https://bigflip.id/big_sandbox_api/v3/disbursement'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        $payloads = [
            "account_number" =>  $payment_method->account_number,
            "bank_code" => $payment_method->bank,
            "amount" => $amount,
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payloads));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/x-www-form-urlencoded",
        "idempotency-key: $ordercode",
        "X-TIMESTAMP: "  .date("Y-m-d\TH:i:s+0700"),
        'Authorization: Basic ' . base64_encode(env('FLIP_SECRET') . ":"),
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response);

        return $json;
    }


    public static function VirtualAccountBCAVersion3($title, $amount, $expired, $redirect, $user_name, $user_email, $user_phone)
    {
        $curl = curl_init();

        $cust_name = slug($user_name);
        $cust_name = str_replace("-", " ", $cust_name);
        $cust_name = strtoupper($cust_name);

        $postfield = ("title=$title&type=SINGLE&expired_date=$expired&amount=$amount&redirect_url=$redirect&step=3&sender_name=$cust_name&sender_email=$user_email&sender_phone_number=$user_phone&sender_bank=bca&sender_bank_type=virtual_account");
        // dd($postfield);

        curl_setopt_array($curl, array(
        CURLOPT_URL => env("FLIP_V2_BASE_URL", 'https://bigflip.id/big_sandbox_api/v2/pwf/bill'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfield,
       
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode(env('FLIP_SECRET') . ":"),
            'Cookie: _csrf=3QvoWWfsNBD_gqFHqxbWfrrehgoSVpeX'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }


    //create payment link
    public static function paymentlink($title, $amount, $expired, $redirect, $user_name, $user_email, $user_phone)
    {
        $curl = curl_init();

        $cust_name = slug($user_name);
        $cust_name = str_replace("-", " ", $cust_name);
        $cust_name = strtoupper($cust_name);

        $postfield = ("title=$title&type=SINGLE&expired_date=$expired&amount=$amount&redirect_url=$redirect&step=2&sender_name=$cust_name&sender_email=$user_email&sender_phone_number=$user_phone");
        // dd($postfield);

        curl_setopt_array($curl, array(
        CURLOPT_URL => env("FLIP_V2_BASE_URL", 'https://bigflip.id/big_sandbox_api/v2/pwf/bill'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfield,
        // CURLOPT_POSTFIELDS => 'title=Evista%20trip%20payment
        // &type=SINGLE
        // &amount=100000
        // &expired_date=2023-07-17%2015%3A30
        // &redirect_url=https%3A%2F%2Fevista.id%2Fpaymentsuccess%2FTRX20230717173000001
        // &step=2
        // &sender_name=Mr.%20Akhyar
        // &sender_email=akhyar%40yopmail.com
        // &sender_phone_number=089671304121
        // ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode(env('FLIP_SECRET') . ":"),
            'Cookie: _csrf=3QvoWWfsNBD_gqFHqxbWfrrehgoSVpeX'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);

    }

    public static function callback($tipe, $body)
    {
        $flip_data_request = (json_decode($body['data'], true));
        $code = str_replace("Evista payment for ", "", $flip_data_request['bill_title']);

        $callback = new \App\TrxCallback;
        $callback->module = "flip-" . $tipe;
        $callback->body = json_encode($body);
        $callback->ordercode = $code;
        $callback->status = $flip_data_request['status'];
        $callback->external_id = $flip_data_request['bill_link_id'];
        $callback->save();


        if ($flip_data_request['status'] == "SUCCESSFUL") {
            $order = \App\Service\OrderService::setpaid($callback->ordercode);
            if (empty($order['error'])) {
                $body['order'] = $order;
            }
        }

        return $body;
    }
}
