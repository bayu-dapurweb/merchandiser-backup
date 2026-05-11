<?php 
namespace App\Service;

use Illuminate\Support\Facades\DB;

class OrderService
{
    public static function createDraftOrder(
        $user_id,
        $order_type //direct , later
    ) {
        $order = \App\TrxOrders::where([
            ['order_type', $order_type],
            ['ref_users_id', $user_id],
            ['trip_status', 'draft']
        ])->first();
        
        if (empty($order)) {
            $order = new \App\TrxOrders;
            $order->ordercode = order_code_generator();
            $order->save();
        }

        $order->order_type = $order_type;
        $order->trip_type = 'single_trip';
        $order->ref_users_id = $user_id;
        $order->transaction_status = "draft";
        $order->trip_status = "draft";
        $order->driver_status = "looking_for_driver";
        $order->pairing_method = "automatic_by_system";
        $order->save();

        return $order;
    }

    public static function createtDraftOrderLocation(
        $user_id,
        $order_id,
        $lat,
        $long,
        $note,
        $label,
        $activity_type,
        $address_book_id
    ) {
        $orderlocation = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order_id],
            ["activity_type", $activity_type],
        ])->orderBy("id","desc")->first();      
        
        // dd($address_book_id);

        if (empty($orderlocation)) {
            $orderlocation = new \App\TrxOrdersLocations;
            $orderlocation->save();
        }

        $orderlocation->trx_order_id = $order_id;            
        $orderlocation->ref_address_book_id = !empty($address_book_id) ? $address_book_id : null;
        $orderlocation->activity_type = $activity_type;
        if ($orderlocation->lat != $lat || $orderlocation->long != $long) {
            $orderlocation->address = \App\Service\LocationService::getAddress($lat, $long);;
        }
        $orderlocation->lat = $lat;
        $orderlocation->long = $long;
        $orderlocation->note = $note;
        $orderlocation->label = $label;
        
        $orderlocation->save();

        return $orderlocation;
    }

    public static function setpaid($orderidentifier)
    {
        $order = \App\TrxOrders::where("ordercode", $orderidentifier)->first();

        if (empty($order)) {
            $order = \App\TrxOrders::find($orderidentifier);
        }

        if (empty($order)) {
            return ['error' => 'invalid order, the order may has beed deleted'];
        }

        // if ($order->transaction_status == "paid") {
        //     return ['error' => 'order already proccess as paid order'];
        // }

        $order->transaction_status = "paid";
        $order->flip_payment_status = "paid";
        $order->flip_paid_at = date("Y-m-d H:i:s");

        if ($order->order_type == "direct") {
            //available driver
            $pickup = $order->pickup($order->id);
            $driver = \App\Service\DriverService::availabledriver($pickup);

            if (empty($driver)) {
                $order->trip_status = "looking_for_driver";
                $order->ref_cars_id = null;
                $order->ref_drivers_id = null;
                $order->pairing_method = "qr code";
                $order->driver_status = "scheduled";
            } else {
                $order->ref_cars_id = $driver->ref_cars_id;
                $order->ref_drivers_id = $driver->id;
                $order->pairing_method = "auto";

                /* if driver auto accept */
                if (\App\Service\DriverService::isAutoAccepted($driver->id) == 1) {
                    $order->driver_status = "accepted";
                    $order->trip_status = "picking up";
                } else {
                    $order->trip_status = "pairing";
                    $order->driver_status = "pairing";
                    $order->pairing_expired_at = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +".setting("PAIRING_TIMEOUT_SECOND", 60)." seconds"));
                }
                // dd($order);
            }
        }
        if ($order->order_type == "later") {
            $order->trip_status = "scheduled";
            $order->pairing_method = "manual";
            $order->driver_status = "looking for driver";   
        }
        if ($order->order_type == "rental") {
            $order->trip_status = "scheduled";
            $order->pairing_method = "manual";
            if ($order->is_with_driver == 1) {
                $order->driver_status = "looking for driver";
            } else {
                $order->driver_status = "no need driver";
            }
        }

        try {
            DB::beginTransaction();
            $cartype = \App\RefCarsTypes::find($order->ref_cars_types_id);
            $order->driver_fee_percent = $cartype->driver_fee;
            $order->driver_fee_value = $order->driver_fee_percent / 100 * $order->basic_price;

            //split order
            if ($order->order_type == "later" && $order->trip_type == "round_trip") {
                //split the fee
                $order->driver_fee_value * 50 / 100;

                //get real meta
                $meta = json_decode($order->meta, true);
                $order->is_splited = 1;

                //clone object
                $orderclone = \App\TrxOrders::where("parent_id", $order->id)->first();
                if (empty($orderclone)) {
                    $orderclone = $order->replicate();
                    $orderclone->parent_id = $order->id;
                }
                if (empty($orderclone->ordercode) || $orderclone->ordercode == $order->ordercode) {
                    $orderclone->ordercode = order_code_generator();
                }
                
                $orderclone->distance = $meta['calculation2_res']['distance'];
                $orderclone->basic_price = $meta['calculation2_res']['price'];
                $orderclone->platform_fee = 0;
                $orderclone->discount_amount = 0;
                $orderclone->grand_total = $orderclone->basic_price;
                $orderclone->driver_fee_value = $order->driver_fee_value;
                $orderclone->save();

                //price split
                $order->basic_price = $meta['calculation_res']['price'];
                $order->grand_total = $order->basic_price + $order->platform_fee - $order->discount_amount;

                //create order location but swaped
                $pickup = $order->pickup($order->id);
                $dropoff = $order->dropoff($order->id);
                \App\Service\OrderService::addOrderLocation($orderclone->id, "pickup", $dropoff->lat, $dropoff->long, $dropoff->note, $dropoff->label, $dropoff->address);
                \App\Service\OrderService::addOrderLocation($orderclone->id, "dropoff", $pickup->lat, $pickup->long, $pickup->note, $pickup->label, $pickup->address);

                
                
            }

            //pairing

            if ($order->driver_status == "pairing") {
                $pairing_log = new \App\TrxOrderPairingLogs;
                $pairing_log->ref_drivers_id = $order->ref_drivers_id;
                $pairing_log->trx_orders_id = $order->id;
                $pairing_log->expired_at = $order->pairing_expired_at;
                $pairing_log->save();
            }

            $order->save();

            if ($order->ref_drivers_id) {
                $driver = \App\RefDrivers::find($order->ref_drivers_id);
                $driver->on_duty_trx_order_id = $order->id;
                $driver->save();
            }

            DB::commit();
            
            // notification payment success for user
            \App\Service\NotificationService::create(
                "Order " . $order->ordercode . " success",
                "Order " . $order->ordercode . " successfully paid, the system will automatically process your order. Thank you for using Evista. Enjoy your trip",
                "order",
                $order->ref_users_id,
                $ref_order_id = $order->id,
                1
            );

            //notif to driver
            if ($order->ref_drivers_id) {
                $driver = \App\RefDrivers::find($order->ref_drivers_id);
                $driver->on_duty_trx_order_id = $order->id;
                $driver->save();

                //driver notification get new order
                \App\Service\NotificationService::create(
                    "You Get New Order, " . $order->ordercode . "",
                    "Order " . $order->ordercode . " congratulation you get a new order",
                    "driver_order",
                    $driver->ref_users_id,
                    $ref_order_id = $order->id,
                    1
                );
            }
            

            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            
            return ['error' => 'Error, major database error', 'data' => $e];
        }
    }

    public static function setexpired($orderidentifier)
    {
        $order = \App\TrxOrders::where("ordercode", $orderidentifier)->first();

        if (empty($order)) {
            $order = \App\TrxOrders::find($orderidentifier);
        }

        if (empty($order)) {
            return ['error' => 'invalid order'];
        }

        if ($order->transaction_status == "paid") {
            return ['error' => 'order proccessed'];
        }

        $order->transaction_status = "expired";
        $order->save();

        // notification here
        \App\Service\NotificationService::create(
            "Order " . $order->ordercode . " expired",
            "Order " . $order->ordercode . " has been expired, You may create new order to continue you journey.",
            "order",
            $order->ref_users_id,
            $ref_order_id = $order->id
        );

        return $order;
    }

    public static function addPromo($order_id ,$promo_id)
    {
        $order = \App\TrxOrders::find($order_id);
        $promo = \App\RefPromos::find($promo_id);
        // dd($promo);

        if (empty($order) || empty($promo)) {
            return false;
        }
        
        $discount_amount = $promo->discount_amount;
        if ($promo->discount_type == "percentage") {
            $discount_amount = ($promo->discount_precentage/100) * $order->basic_price;
        }
        

        if ($promo->min_transaction_mount > $order->basic_price) {
            $discount_amount = 0;
        }

        if ($promo->max_discount_amount < $discount_amount) {
            $discount_amount = $promo->max_discount_amount;
        }
        
        $order_promo = \App\TrxOrdersPromosRedeems::where("trx_order_id", $order_id)->orderBy("id", "desc")->first();
        if (empty($order_promo)) {
            $order_promo = new \App\TrxOrdersPromosRedeems;
        }
        
        $order_promo->ref_users_id = $order->ref_users_id;
        $order_promo->trx_order_id = $order->id;
        $order_promo->ref_promos_id = $promo->id;
        $order_promo->redeemed_amount = $discount_amount;
        $order_promo->save();

        return $order_promo;
    }

    public static function addMorePromo($order_id ,$promo_id)
    {
        $order = \App\TrxOrders::find($order_id);
        $promo = \App\RefPromos::find($promo_id);
        // dd($promo);

        if (empty($order) || empty($promo)) {
            return false;
        }

        $exist = \App\TrxOrdersPromosRedeems::where([
            ['ref_users_id', $order->ref_users_id],
            ['trx_order_id', $order->id],
            ['ref_promos_id', $promo->id],
        ])->first();

        if (!empty($exist)) {
            return $exist;
        }

        if ($promo->promo_group == "porter") {
            $porter_promo = \App\TrxOrdersPromosRedeems::where([
                ['ref_users_id', $order->ref_users_id],
                ['trx_order_id', $order->id],
            ])->whereRaw("ref_promos_id IN (
                SELECT id 
                FROM ref_promos
                WHERE promo_group = 'porter'
            )")->first();

            if (!empty($porter_promo)) {
                return $porter_promo;
            }
        }
        
        /* if promo porter, discount amount is from the setting */
        $fee_amount = 0;
        if ($promo->promo_group == "porter") {
            $discount_amount = setting('PORTER_PROMO_DISCOUNT_FOR_USER', 5000);
            $fee_amount = setting('PORTER_REFERRAL_FEE', 10000);
        } else {
            $discount_amount = $promo->discount_amount;
            if ($promo->discount_type == "percentage") {
                $discount_amount = ($promo->discount_precentage/100) * $order->basic_price;
            }
            
            if ($promo->min_transaction_mount > $order->basic_price) {
                $discount_amount = 0;
            }

            if ($promo->max_discount_amount < $discount_amount) {
                $discount_amount = $promo->max_discount_amount;
            }
        }
        
        
        $order_promo = new \App\TrxOrdersPromosRedeems;
        $order_promo->ref_users_id = $order->ref_users_id;
        $order_promo->trx_order_id = $order->id;
        $order_promo->ref_promos_id = $promo->id;
        $order_promo->redeemed_amount = $discount_amount;
        $order_promo->fee_amount = $fee_amount;
        $order_promo->save();

        return $order_promo;
    }

    public static function getOrderPromosAmount($order_id)
    {
        $order = \App\TrxOrders::find($order_id);
        if (empty($order)) {
            return 0;
        }

        $order_promo = \App\TrxOrdersPromosRedeems::where("trx_order_id", $order_id)->orderBy("id", "desc")->first();

        if (!empty($order_promo))  {
            return $order_promo->redeemed_amount;
        } else {
            return 0;
        }
        
    }

    public static function getOrderActivatedPromos($order_id)
    {
        $order = \App\TrxOrders::find($order_id);
        if (empty($order)) {
            return [];
        }

        $promos = \App\TrxOrdersPromosRedeems::with("promos")
            ->where("trx_order_id", $order_id)
            ->get()->map(function($r){
                // $r->promos->itle
                if ($r->promos->promo_group == "porter") {
                    $r->promos->title = $r->promos->title . ", Porter Discount Rp" . nominal(setting("PORTER_PROMO_DISCOUNT_FOR_USER", 1000));
                    $r->promos->start_at = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " -1 hours"));
                    $r->promos->end_at = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +1 hours"));
                    $r->promos->discount_amount = (setting("PORTER_PROMO_DISCOUNT_FOR_USER", 1000));
                }
                return $r;
            });

        return $promos;
    }

    public static function getOrderPromosAllAmount($order_id)
    {
        $order = \App\TrxOrders::find($order_id);
        if (empty($order)) {
            return 0;
        }

        $order_promo = \App\TrxOrdersPromosRedeems::where("trx_order_id", $order_id)->get();
        $total = 0;
        foreach ($order_promo as $v) {
            $total += $v->redeemed_amount;
        }

        return $total;
    }

    public static function addOrderLocation(
        $order_id, 
        $pickup_dropoff,
        $lat,
        $long,
        $note,
        $label,
        $address = ""
    ) {
        $location = \App\TrxOrdersLocations::where([
            ["trx_order_id", $order_id],
            ["activity_type", $pickup_dropoff]
        ])->first();

        if (empty($location)) {
            $location = new \App\TrxOrdersLocations;
            $location->trx_order_id = $order_id;
            $location->activity_type = $pickup_dropoff;
        }
        $location->lat = $lat;
        $location->long = $long;
        $location->note = $note;
        $location->label = $label;
        $location->address = $address;
        $location->save();

        return $location;
    }


    public static function orderSetComplete($order_id)
    {
        $order = \App\TrxOrders::find($order_id);
        $driver = \App\RefDrivers::find($order->ref_drivers_id);

        if (empty($order)) {
            return false;
        }

        if ($order->trip_status == "complete") {
            return ['error' => 'order already complete'];
        }
        
        try {
            DB::beginTransaction();

            $order->trip_status = "complete";
            $order->complete_trip_at = date("Y-m-d H:i:s");
            $order->save();


            if (!empty($driver)) {
                $driver->on_duty_trx_order_id = null;
                $driver->save();

                //get the fee to driver's wallet log
                /*
                $driver_wallet_log = new \App\LogWallets;
                $driver_wallet_log->ref_users_id = $driver->ref_users_id;
                $driver_wallet_log->nominal = $order->driver_fee_value;
                $driver_wallet_log->in_out = "in";
                $driver_wallet_log->order_id = $order->id;
                $driver_wallet_log->save();
                */

                //driver wallet
                /*
                $wallet = \App\RefWallets::where("ref_users_id", $driver->ref_users_id)->first();
                if (empty($wallet)) {
                    $wallet = new \App\RefWallets;
                    $wallet->ref_users_id = $driver->ref_users_id;
                }
                $wallet->nominal = $wallet->nominal + $driver_wallet_log->nominal;
                $wallet->save();
                */
            }

            DB::commit();
            
            //notification
            \App\Service\NotificationService::create(
                "Order " . $order->ordercode . " has been complete",
                "Order " . $order->ordercode . " has been complete, thank you for using evista.",
                "order",
                $order->ref_users_id,
                $ref_order_id = $order->id,
                1
            );

            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            
            return ['error' => 'Error, major database error', 'data' => $e];
        }

        return $order;
    }

    public static function pairNextDrivers($order_id)
    {
        $order = \App\TrxOrders::find($order_id);

        try {
            DB::beginTransaction();

            //exclude that have been paired before
            $paired_driver = \App\TrxOrderPairingLogs::where("trx_orders_id", $order_id)
            ->get()
            ->pluck('ref_drivers_id')
            ->toArray();

            //search available driver
            $driver = \App\Service\DriverService::availabledriver($pickup, $paired_driver);

            //there any driver
            $is_cancel = 0;
            if (!empty($driver)) {
                $order->ref_drivers_id = $driver->id;
                $order->pairing_method = "auto";
                if (\App\Service\DriverService::isAutoAccepted($driver->id) == 1) {
                    $order->driver_status = "accepted";
                    $order->trip_status = "picking up";
                } else {
                    $order->trip_status = "pairing";
                    $order->driver_status = "pairing";
                    $order->pairing_expired_at = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +".setting("PAIRING_TIMEOUT_SECOND", 60)." seconds"));
                }
                $driver->on_duty_trx_order_id = $order->id;

                $order->save();
                $driver->save();

                $pairing_log = new \App\TrxOrderPairingLogs;
                $pairing_log->ref_drivers_id = $order->ref_drivers_id;
                $pairing_log->trx_orders_id = $order->id;
                $pairing_log->expired_at = $order->pairing_expired_at;
                $pairing_log->save();
            } else {
                //find no driver
                $is_cancel = 1;
                $order->trip_status = "canceled";
                $order->driver_status = "canceled";
                $order->transaction_status = "canceled";
                $order->save();
            }
            DB::commit();

            if ($is_cancel) {
                //customer notification order cancel
                \App\Service\NotificationService::create(
                    "Order " . $order->ordercode . " canceled",
                    "Order " . $order->ordercode . " has been canceled, You may create new order to continue you journey.",
                    "order",
                    $order->ref_users_id,
                    $ref_order_id = $order->id
                );
            } else {
                //driver notification get new order
                \App\Service\NotificationService::create(
                    "You Get New Order, " . $order->ordercode . "",
                    "Order " . $order->ordercode . " congratulation you get a new order",
                    "driver_order",
                    $driver->ref_users_id,
                    $ref_order_id = $order->id,
                    1
                );
            }
            

            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            
            return ['error' => 'Error, major database error', 'data' => $e];
        }

    }

    
}