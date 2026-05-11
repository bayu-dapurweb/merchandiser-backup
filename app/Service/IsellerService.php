<?php 

namespace App\Service;

class IsellerService
{
    public static function apiGetCall($url, $body = null)
    {
        $error = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Content-Length: " . strlen($data)
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/plain',
                'Expect:'
            ]);
        }

        $response = null;
        if (env('SYNC_ENABLE', true)) {
            $response = curl_exec($ch);

            // Check if there was an error
            if (curl_errno($ch)) {
                $error = curl_error($ch);
            }

            // Get the HTTP status code
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }

        curl_close($ch);

        // If there was an error, you may handle it here (e.g., logging, throwing an exception)
        if ($error) {
            return [
                'data' => $error,
                'code' => 500
            ];
        }

        // Return both the response body and status code
        return [
            'data' => json_decode($response, true),
            'code' => $httpStatusCode
        ];
    }

    
    public static function apiGetQuery($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Basic ' . env('SAP_BASIC_AUTH')]);
        if (env('SAP_SYNC_ENABLE', true)) {
            $response = curl_exec($ch);
        }
        
        $error = false;
        if (curl_errno($ch)) {
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


        $headers = array(
            'Content-Type: text/plain',
            'Cookie: B1SESSION='.$loginres->SessionId.'; ROUTEID=.node4; sapxslb=3740739B65C2DC4C851BC97F9D82E87E; xsSecureId8DBB9139262815ECAEDDB3F374278EC3=93E7E7B01BF36F4DBF6BEDF70B120BA0',
            'Expect:'
        );

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

        if (env('SYNC_ENABLE', true)) {
            $response = curl_exec($ch);

            // Check if there was an error
            if (curl_errno($ch)) {
                $error = curl_error($ch);
            }

            // Get the HTTP status code
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }

        curl_close($ch);

        // If there was an error, you may handle it here (e.g., logging, throwing an exception)
        if ($error) {
            return [
                'data' => $error,
                'code' => 500
            ];
        }

        // Return both the response body and status code
        return [
            'data' => json_decode($response, true),
            'code' => $httpStatusCode
        ];

    }

    public function apiPostCallFormData($url, $data, $custom_method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_stringfy);
        if (env('SAP_SYNC_ENABLE', true)) {
            $response = curl_exec($ch);
        }

        if (env('SYNC_ENABLE', true)) {
            $response = curl_exec($ch);

            // Check if there was an error
            if (curl_errno($ch)) {
                $error = curl_error($ch);
            }

            // Get the HTTP status code
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }

        curl_close($ch);

        // If there was an error, you may handle it here (e.g., logging, throwing an exception)
        if ($error) {
            return [
                'data' => $error,
                'code' => 500
            ];
        }

        // Return both the response body and status code
        return [
            'data' => json_decode($response, true),
            'code' => $httpStatusCode
        ];

    }

    public static function getProducts($page = 1, $size = 200, $start_date = "2025-01-01")
    {
        $url = env('ISELLER_HOST') . '/api/v2/GetProducts';
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'track_inventory=true&modified_after='.$start_date.'0T17%3A00%3A00&includes=tags&page='.$page.'&page_size=' . $size,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . cache_get("ISELLER_ACCESS_TOKEN")
        ),
        ));
        
        $response = null;
        if (env('SYNC_ENABLE', true)) {
            $response = curl_exec($curl);

            // Check if there was an error
            if (curl_errno($curl)) {
                $error = curl_error($curl);
            }

            // Get the HTTP status code
            $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        curl_close($curl);

        // If there was an error, you may handle it here (e.g., logging, throwing an exception)
        if ($error) {
            return [
                'data' => $error,
                'code' => 500
            ];
        }

        // Return both the response body and status code
        return [
            'data' => json_decode($response, true),
            'code' => $httpStatusCode
        ];

    }

    public static function getItemMasterData($with_debug = false)
    {
        $page = 1;
        $call_execute = true;

        if ($with_debug) {
            echo "# initiate sync \n";
        }
        while($call_execute) {
            if ($with_debug) {
                echo "# curl page $page \n";
            }
            $res = self::getProducts($page);

            $log = new \App\LogApiCalls;
            $log->related_module = 'ref_item_master_datas';
            $log->related_reff_id = null;
            $log->api_url = env('ISELLER_HOST') . '/api/v2/GetProducts';
            $log->request_body = 'track_inventory=true&modified_after=2025-01-010T17%3A00%3A00&includes=tags&page='.$page.'&page_size=200';
            $log->response_body = json_encode($res['data']);
            $log->response_code = $res['code'];
            $log->save();

            

            if ($res['code'] != 200) {
                if ($with_debug) {
                    echo "# curl error ".$res['code']." \n";
                }
                break;
            }
            if (!empty($res['data']['products'])) {
                foreach ($res['data']['products'] as $v) {
                    if ($with_debug) {
                        echo "# save item ".$v['sku']." \n";
                    }
                    $item = \App\RefItemMasterData::where('sku', $v['sku'])->first();
                    if (empty($item)) {
                        $item = new \App\RefItemMasterData;
                    }
                    
                    $item->product_header_id = $v['product_header_id'];
                    $item->product_type = $v['product_type'];
                    $item->vendor = $v['vendor'];
                    // $item->attribute = $v['attribute'];
                    $item->tags = json_encode($v['tags']);
                    $item->product_id = $v['product_id'];
                    $item->name = $v['name'];
                    $item->type = $v['type'];
                    $item->barcode = $v['barcode'];
                    $item->sku = $v['sku'];
                    
                    $item->price = $v['price'];
                    // $item->outlet_prices = $v['outlet_prices'];
                    $item->taxable = $v['taxable'];
                    $item->track_inventory = $v['track_inventory'];
                    $item->allow_negative_stock = $v['allow_negative_stock'];
                    $item->sold_count = $v['sold_count'];
                    $item->unit_of_measurement = $v['unit_of_measurement'];
                    // $item->buying_prices = $v['buying_prices'];
                    $item->buying_price = $v['buying_price'];
                    $item->inventories = json_encode($v['inventories']);
                    $item->ingredients = json_encode($v['ingredients']);
                    $item->variant_options = json_encode($v['variant_options']);
                    $item->bundles = json_encode($v['bundles']);
                    $item->is_active = $v['is_active'];
                    $item->modified_date = $v['modified_date'];
                    $item->save();
                }

                $page++;
            } else {
                if ($with_debug) {
                    echo "# curl empty data, cut it \n";
                }
                $call_execute = false;
            }
        }
    }


    public static function iSellerPost($action_url, $body)
    {
        $url = env('ISELLER_HOST') . $action_url;
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . cache_get("ISELLER_ACCESS_TOKEN")
        ),
        ));
        
        $response = null;
        if (env('SYNC_ENABLE', true)) {
            $response = curl_exec($curl);

            // Check if there was an error
            if (curl_errno($curl)) {
                $error = curl_error($curl);
            }

            // Get the HTTP status code
            $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        curl_close($curl);

        // If there was an error, you may handle it here (e.g., logging, throwing an exception)
        if ($error) {
            return [
                'data' => $error,
                'code' => 500
            ];
        }

        // Return both the response body and status code
        return [
            'data' => json_decode($response, true),
            'code' => $httpStatusCode
        ];

    }

    public static function getWithBody($url, $body)
    {

        $jsonData = json_encode($body);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen($jsonData)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 0); // equivalent to --no-buffer
        curl_setopt($ch, CURLOPT_VERBOSE, true); // show error equivalent

        $response = null;
        if (env('SYNC_ENABLE', true)) {
            $response = curl_exec($ch);

            // Check if there was an error
            if (curl_errno($ch)) {
                $error = curl_error($ch);
            }

            // Get the HTTP status code
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }

        curl_close($ch);

        // If there was an error, you may handle it here (e.g., logging, throwing an exception)
        if ($error) {
            return [
                'data' => $error,
                'code' => 500
            ];
        }

        // Return both the response body and status code
        return [
            'data' => json_decode($response, true),
            'code' => $httpStatusCode
        ];
    }
}