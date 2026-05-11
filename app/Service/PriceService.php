<?php 
namespace App\Service;

use App\RefPricelist;
use App\RefPricelistVariants;

class PriceService
{
    public static function getPrice($order_type, $trip_type, $car_type_id, $distance, $is_with_driver = 0)
    {

        // dd($order_type, $trip_type, $car_type_id, $distance, $is_with_driver);

        $date_now = date("Y-m-d H:i:s");
        
        if ($order_type == "direct") {
            $transaction_type = "direct_single_trip";
        }
        if ($order_type == "later" && $trip_type == "single_trip") {
            $transaction_type = "later_single_trip";
        }
        if ($order_type == "later" && $trip_type == "round_trip") {
            $transaction_type = "later_round_trip";
        }
        if ($order_type == "rental" && $is_with_driver == 1) {
            $transaction_type = "rental_with_driver";
        } else if ($order_type == "rental") {
            $transaction_type = "rental_no_driver";
        }
        //search for mask price
        $price = RefPricelist::where([
            ["transaction_type", $transaction_type],
            ["ref_cars_types_id", $car_type_id],
            ["is_default", 0]
        ])->whereRaw("start_at <= '$date_now' and end_at >= '$date_now'")
        ->orderBy("id", "desc")
        ->first();

        // dd($price, [["transaction_type", $transaction_type],
        // ["ref_cars_types_id", $car_type_id],
        // ["is_default", 0]]);

        if (empty($price)) {
            $price = RefPricelist::where([
                ["transaction_type", $transaction_type],
                ["ref_cars_types_id", $car_type_id],
                ["is_default", 1]
            ])->first();
        }

        if (empty($price)) {
            return false;
        }

        //select for the variant
        $variants = RefPricelistVariants::where("ref_pricelists_id", $price->id)
        // ->whereRaw("$distance >= lower_limiit and $distance <= upper_limit ")
        ->orderBy("upper_limit", "desc")
        ->get();

        

        $final_price = 0;
        if (!empty($variants)) {
            foreach ($variants as $v) {
                if ($distance >= $v->lower_limiit && $distance >= $v->upper_limit) {
                    $final_price = $v->price + (($distance - $v->upper_limit) * $price->price);
                } else if ($distance >= $v->lower_limiit && $distance <= $v->upper_limit) {
                    $final_price = $v->price;
                }
            }
        }

        if ($final_price == 0) {
            $final_price =  $price->price * $distance;
        }

        //rental additional price
        if ($order_type == "rental" && $is_with_driver == 0) {
            $additonal = setting("RENTAL_ADDITIONAL_PRICE", 100000);
            $final_price = $final_price + $additonal;
        }

        //spike price
        $price_setting = \App\Settings::where([
            ["key", "PRICE_SPIKE"],
            ["setting_type", "background"]
        ])->first();

        if (!empty($price_setting)) {
            $procentage = $price_setting->value / 100;
        } else {
            $procentage = 1;
        }

        $final_price = $procentage * $final_price;

        $final_price = round($final_price);

        $final_price = (int)$final_price;

        return $final_price;
    }

    public static function fullCalculate(
        $origin_lat,
        $origin_long,
        $dest_lat,
        $dest_long,
        $car_type,
        $order_type,
        $trip_type
    ) {
        //calculate distance
        $distance = $realdistance = \App\Service\LocationService::distance(
            $origin_lat,
            $origin_long,
            $dest_lat,
            $dest_long
        );

        if ($distance === false) {
            $GLOBALS['route_error'] = 1;
            return false;
        }

        $distance = $distance < 1 ? 1 : (int)ceil($distance);

        //calculate the price
        $price = \App\Service\PriceService::getPrice(
            $order_type,
            $trip_type,
            $car_type,
            $distance    
        );

        return [
            'distance' => $distance,
            'realdistance' => $realdistance,
            'price' => $price,
        ];
    }


    public static function rentalCalculate(
        $pickup_at,
        $return_at,
        $is_with_driver,
        $order_type,
        $car_type_id
    ) {
        // dd($pickup_at,
        // $return_at,
        // $is_with_driver,
        // $order_type,
        // $car_type_id);
        //qty calculation
        if ($is_with_driver == 1) {
            //with driver, max 18 hours, min 12 hours, more than 12 hours qty is 2
            $date1=date_create($pickup_at);
            $date2=date_create($return_at);
            $diff=date_diff($date1,$date2);
            $hour_diff = $diff->format("%h");
            
            if ($hour_diff > 12) {
                $qty = 2;
            } else {
                $qty = 1;
            }
            
        } else {
            //after 00:00 count as additional 1 day.
            $date1=date_create(date("Y-m-d", strtotime($pickup_at)));
            $date2=date_create(date("Y-m-d", strtotime($return_at)));
            $diff=date_diff($date1,$date2);
            $day_diff = $diff->format("%a");
            $day_diff++;
            
            $qty = $day_diff;
        }
        
        //calculate the price
        $price = \App\Service\PriceService::getPrice(
            $order_type,
            "single_trip",
            $car_type_id,
            $qty,
            $is_with_driver
        );

        return [
            'distance' => $qty,
            'realdistance' => $qty,
            'price' => $price,
        ];
    }
}
