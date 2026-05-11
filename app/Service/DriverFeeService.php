<?php 
namespace App\Service;

class DriverFeeService
{
    public static function executecutoff($cursordate = "")
    {
        if (empty($cursordate)) {
            $cursordate = date("Y-m-d");
        }
        $orders = \App\TrxOrders::
        whereRaw("date(flip_paid_at) = '$cursordate'")
        ->whereRaw("ref_drivers_id is not null")
        ->where("transaction_status", "paid")
        ->where("order_type", "!=", "rental")
        ->where("is_process_on_cut_off", "0")
        ->get();

        try {
            \DB::beginTransaction();

            $driver_fee_group = [];
            $additional_charge = [];
            foreach ($orders as $v) {
                $v->is_process_on_cut_off = 1;
                $v->save();

                /* transaction */
                @$driver_fee_group[$v->ref_cars_types_id][$v->ref_drivers_id]['total_income'] += $v->grand_total;
                @$driver_fee_group[$v->ref_cars_types_id][$v->ref_drivers_id]['meta'][] = [
                    'order' => $v,
                    'order_id' => $v->id,
                    'basic_price' => $v->grand_total,
                ];

                /* additional charge */
                $additional_charge_total = 0;
                $additional = \App\TrxOrdersAdditionalPayments::where([
                    ["trx_orders_id", $v->id],
                    ["is_process_on_cut_off", 0]
                ])
                ->orderBy("id", "desc")
                ->first();

                if (!empty($additional)) {
                    
                    $additional_charge_total += $additional->toll;
                    $additional_charge_total += $additional->parking;
                    $additional_charge_total += $additional->others;
                    $additional_charge_total += $additional->tips;
                    @$additional_charge[$v->ref_drivers_id]['total'] = $additional_charge_total;
                    @$additional_charge[$v->ref_drivers_id]['meta'] = [
                        'additional_id' => $additional->id
                    ];

                    $additional->is_process_on_cut_off = 1;
                    $additional->save();
                }
                
            }

            \Log::debug("DRIVER_FEE_GROUP_VAR:" . json_encode($driver_fee_group));
            $wallet_debug = [];
            foreach ($driver_fee_group as $car_types_id => $driver_income) {
                $progresive = \App\RefCartypeProgresiveFee::where("ref_cars_types_id", $car_types_id)->get();
                
                foreach ($driver_income as $driver_id => $income) {
                    //hitung fee
                    $total_income = $income['total_income'];
                    $total_ritase = count($income['meta']);
                    $total_income = $total_income - ($total_ritase * setting('KONSESI_PUSKOP_FEE', 15000));
                    $final_fee = 0;
                   
                    foreach ($progresive as $prog) {
                        if ($total_income >= $prog->lower_limit_nominal) {
                            
                            if ($total_income <= $prog->upper_limit_nominal) {
                                $final_fee += $total_income * $prog->percentage_fee / 100;
                            }
                            if ($total_income >= $prog->upper_limit_nominal) {
                                $final_fee += $prog->upper_limit_nominal * $prog->percentage_fee / 100;
                                $total_income =$total_income - $prog->upper_limit_nominal;
                            }
                        }
                    }

                    $final_fee = $final_fee - setting("CUCI_MOBIL_FEE", 10000);
                    $driver_fee_group[$car_types_id][$driver_id]['driver_income'] = $final_fee;
                    $meta['order_at'] = $cursordate;
                    $meta['orders'] = $driver_fee_group[$car_types_id][$driver_id]['meta'];
                    $meta['cartypes_id'] = $car_types_id;
                    $meta['total_income'] = $total_income;
                    $meta['final_fee'] = $final_fee;
                    $driver = \App\RefDrivers::find($driver_id);

                    //insert fee log
                    \Log::debug("DRIVER_FEE_ADD_BALLANCE:" . json_encode([
                        "driver" => $driver,
                        "final_fee" => $final_fee,
                        "meta" => $meta,
                    ]));


                    if (!empty($driver)) {
                        $wallet_debug[] = [
                            $driver,
                            $final_fee,
                            $meta
                        ];
                        \App\RefWallets::addballance($driver, $final_fee, $meta);    

                        if (!empty($additional_charge[$driver->id]['total'])) {
                            
                            \Log::debug("DRIVER_FEE_ADD_ADDITIONAL_CHARGE:" . json_encode([
                                "driver" => $driver,
                                "final_fee" => $additional_charge[$driver->id]['total'],
                                "meta" => $additional_charge[$driver->id]['meta'],
                            ]));
                            \App\RefWallets::addballance(
                                $driver, 
                                $additional_charge[$driver->id]['total'], 
                                $additional_charge[$driver->id]['meta']
                            ); 

                            unset($additional_charge[$driver->id]['total']);
                            unset($additional_charge[$driver->id]['meta']);
                        }
                    }
                    
                }                
            }

            \DB::commit();
            return [
                'status' => "success",
                'message' => 'Program executed well',
                'data' => $driver_fee_group
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::debug("DRIVER_FEE_CUTOFF:" . json_encode($e));
            \Log::debug("DRIVER_FEE_CUTOFF_RAW:" . ($e));

            return [
                'status' => "error",
                'message' => 'Error, major database error',
                'data' => $e
            ];
        }
    }
}