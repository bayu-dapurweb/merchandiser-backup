<?php 
namespace App\Service;

use Illuminate\Support\Facades\Log;

class TripService
{
    public static function nearPickupPoint($current_lat, $current_long)
    {
        $rad = env("HARD_RADIUS", $rad);
        \App\Service\RedisService::loadpickuppoint();
        $client = \App\Service\RedisService::client();
        $response = $client->executeRaw(['GEORADIUS', redisprevix() . 'pickup', $current_long, $current_lat, $rad, 'km', 'withdist']);

        $distance_by_id = [];
        $id_list = [];

        Log::debug("geo radius near pickup point:" . json_encode($response));
        if ($response == 'ERR value is not a valid float') {
            $pickup_points = \App\RefPickupPoints::get()
            ->map(function($r) use ($distance_by_id) {
                $r->distance_raw = 0;
                $r->distance_label = "Too far";
                $r->icon = uri($r->icon);
                return $r;
            });

            return $pickup_points;
        }
        if (!empty($response)) {
            foreach ($response as $v) {
                $distance_by_id[$v[0]] = $v[1];
                $id_list[] = $v[0];
            }
            $pickup_points = \App\RefPickupPoints::get()
            ->map(function($r) use ($distance_by_id) {
                $r->distance_raw = $distance_by_id[$r->id];
                $r->distance_label = $distance_by_id[$r->id] > 1 ? (int)round($distance_by_id[$r->id],2) : "Less than 1 Km";
                $r->icon = uri($r->icon);
                return $r;
            });

            return $pickup_points;
        } else {
            $pickup_points = \App\RefPickupPoints::get()
            ->map(function($r) use ($distance_by_id) {
                $r->distance_raw = 0;
                $r->distance_label = "Too far";
                $r->icon = uri($r->icon);
                return $r;
            });

            return $pickup_points;
        }
        

        //note re result need to be sort by id field -> https://stackoverflow.com/questions/396748/ordering-by-the-order-of-values-in-a-sql-in-clause
    }


    public static function setPickupPoint($user_id, $order_type, $pickup_point_id)
    {
        //create or replace draft order and set the pickup location
        $order = \App\Service\OrderService::createDraftOrder($user_id, $order_type);

        //order pickup locations create or replace
        $orderlocation = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order->id],
            ["activity_type", "pickup"],
        ])->first();
        if (empty($orderlocation)) {
            $orderlocation = new \App\TrxOrdersLocations;
        }

        $pickup_points = \App\RefPickupPoints::find($pickup_point_id);

        $orderlocation->trx_order_id = $order->id;
        $orderlocation->activity_type = "pickup";
        $orderlocation->lat = $pickup_points->lat;
        $orderlocation->long = $pickup_points->long;
        $orderlocation->label = $pickup_points->name;
        $orderlocation->save();

        $order->pickup_point = $orderlocation;

        //destination if exist
        $dropoff_point = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order->id],
            ["activity_type", "dropoff"],
        ])->orderBy("id", "desc")->first();

        $order->dropoff_point = $dropoff_point;

        return $order;
    }

    public static function submit(
        $order_type,
        $user_id,
        $pickup_at = "",
        $return_at = "",
        $is_with_driver = "",
        $is_same_return_location = ""
    ) {
        $order = \App\TrxOrders::where([
            ["order_type", $order_type],
            ["ref_users_id", $user_id],
        ])->whereIn("transaction_status", ["draft", "selecting_car"])
        ->orderBy("updated_at", "desc")->first();

        if (empty($order)) {
            return false;
        }
        
        if ($order_type == "later") {
            $order->pickup_at = $pickup_at;
            $order->return_at = $return_at;
        }
        
        if ($order_type == "rental") {
            $order->pickup_at = $pickup_at;
            $order->return_at = $return_at;
            $order->is_with_driver = $is_with_driver;
            $order->is_same_return_location = $is_same_return_location;
        }

        
        if ($is_same_return_location == 1) {
            $orderlocation = \App\TrxOrdersLocations::where([
                ["trx_order_id", $order->id],
                ["activity_type", "pickup"],
            ])->first();
            $dropoff_point = \App\TrxOrdersLocations::where([
                ["trx_order_id", $order->id],
                ["activity_type", "dropoff"],
            ])->first();

            $dropoff_point->lat = $orderlocation->lat;
            $dropoff_point->long = $orderlocation->long;
            $dropoff_point->label = $orderlocation->label;
            $dropoff_point->address = $orderlocation->address;
            $dropoff_point->save();
        }

        $order->transaction_status = "selecting_car";
        $order->save();

        //create active promo
        $orderpromo = \App\TrxOrdersPromosRedeems::where("trx_order_id", $order->id);
        if (!empty($orderpromo)) {
            foreach ($orderpromo as $o) {
                $o->delete();
            }
        }

        $orderlocation = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order->id],
            ["activity_type", "pickup"],
        ])->first();
        $dropoff_point = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order->id],
            ["activity_type", "dropoff"],
        ])->first();

        $order->pickup = $orderlocation;
        $order->dropoff = $dropoff_point;

        return $order;
    }

    public static function activeOrder($user_id, $order_type)
    {
        $order = \App\TrxOrders::where([
            ["order_type", $order_type],
            ["ref_users_id", $user_id],
            ["transaction_status", "draft"]
        ])->whereNotIn('trip_status', ['complete', 'cancel', 'ontrip'])
        ->first();

        if (empty($order)) {
            return null;
        }

        $orderlocation = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order->id],
            ["activity_type", "pickup"],
        ])->first();
        
        $dropoff_point = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order->id],
            ["activity_type", "dropoff"],
        ])->first();

        $order->pickup = $orderlocation;
        $order->dropoff = $dropoff_point;

        return $order;
    }
}