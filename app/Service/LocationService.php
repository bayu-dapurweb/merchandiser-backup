<?php 
namespace App\Service;

use Illuminate\Support\Facades\Log;

class LocationService
{
    public static function lastvisit($user_id, $id = 0)
    {
        $locations = \App\TrxOrdersLocations::whereRaw("trx_order_id IN (
            SELECT trx_orders.id 
            FROM trx_orders 
            WHERE trx_orders.ref_users_id = '$user_id'
        )");
        if (!empty($id)) {
            $locations = $locations->where("id", $id);
        }
        $locations = $locations->get();

        $list = [];
        foreach ($locations as $v) {
            $list[md5($v->lat . $v->long)] = [
                'id'    => $v->id,
                'label' => $v->label,
                'lat'   => $v->lat,
                'long'  => $v->long,
                'address'  => $v->address,
            ];
        }

        $cleanlit = [];
        foreach ($list as $v) {
            $cleanlit[] = $v;
        }

        return $cleanlit;
    }

    public static function selectDestination(
        $user_id,
        $order_type,
        $activity_type,
        $lat,
        $long,
        $note,
        $label,
        $address_book_id
    ) {
        //create draft order if user dont have any order
        $order = \App\Service\OrderService::createDraftOrder(
            $user_id,
            $order_type
        );

        //creatae draft order location if user dont have any
        $order_location = \App\Service\OrderService::createtDraftOrderLocation(
            $user_id,
            $order->id,
            $lat,
            $long,
            $note,
            $label,
            $activity_type,
            $address_book_id
        );

        return $order_location;
    }

    public static function getActiveDestination($user_id)
    {
        $order = \App\TrxOrders::where([
            ["ref_users_id", $user_id],
            ["transaction_status", "draft"]
        ])->first();
        $orderlocation = \App\TrxOrdersLocations::where([
            ["activity_type", "dropoff"],
            ["trx_order_id", $order->id],
        ])->orderBy("id","desc")->first(); 
        return $orderlocation;
    }

    public static function getAddress($lat, $long)
    {
        if (!empty($lat) && !empty($long)) {
            /* has been here */
            $location = \App\TrxOrdersLocations::where([
                ['lat', $lat],
                ['long', $long],
            ])->first();
            
            if (!empty($location)) {
                return $location->address;
            }

            /* not found on the database, then search on the google map */
            $curl = curl_init();

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$long.'&key=' . env('GOOGLE_PLACE_API_KEY') . '&enable_address_descriptor=true';

            Log::debug("google-place-destination-url:" . $url);

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            

            // Log::debug("google-place-destination-res:" . $response);

            curl_close($curl);

            $json = json_decode($response, true);
            return ($json['results'][0]['formatted_address']);
            

        } else {
            return "-";
        }
    }

    public static function removeOrderLocaton($order_id, $activity_type)
    {
        $location = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order_id],
            ["activity_type", $activity_type]
        ])->first();

        if (empty($location)) {
            return false;
        }

        $location->delete();

        return $location;
    }

    public static function distance($from_lat, $from_long, $to_lat, $to_long)
    {
        if (env('MANUAL_LOCATION_SEACH', false)) {
            $distance = \App\Service\RedisService::distance(
                "manual_disctance",
                (object)[
                    'id' => random_string(),
                    'lat' => $from_lat,
                    'long' => $from_long,
                ],
                (object)[
                    'id' => random_string(),
                    'lat' => $to_lat,
                    'long' => $to_long,
                ],
                "km"
            );

            $param = json_encode(["manual_disctance",
            (object)[
                'id' => random_string(),
                'lat' => $from_lat,
                'long' => $from_long,
            ],
            (object)[
                'id' => random_string(),
                'lat' => $to_lat,
                'long' => $to_long,
            ],
            "km"]);

            Log::debug("manual-distance-param:" . ($param));
            Log::debug("manual-distance-res:" . ($distance));

            if ($distance) {
                $distance = 130 / 100 * $distance;
                return $distance;
            } else {
                return false;
            }
        }

        // Set your API key and endpoints
        $apiKey = env('GOOGLE_PLACE_API_KEY');
        $baseUrl = 'https://maps.googleapis.com/maps/api/directions/json';

        // Origin and destination coordinates (latitude and longitude)
        $originLat = $from_lat;
        $originLng = $from_long;
        $destinationLat = $to_lat;
        $destinationLng = $to_long;

        // Build the API request URL
        $requestUrl = "$baseUrl?origin=$originLat,$originLng&destination=$destinationLat,$destinationLng&mode=driving&key=$apiKey";
        // $response = file_get_contents($requestUrl);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $requestUrl,
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

        // Check if the request was successful
        if ($data['status'] === 'OK') {
            // distance is in KM
            $distance = $data['routes'][0]['legs'][0]['distance']['value'] / 1000;
            
            return $distance;
        } else {

            $callback = new \App\TrxCallback;
            $callback->module = "google map distance";
            $callback->body = $response;
            $callback->save();

            return false;
        }
    }
}