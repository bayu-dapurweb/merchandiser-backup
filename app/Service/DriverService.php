<?php 
namespace App\Service;

class DriverService
{
    public static function availabledriver($pickup, $exclude_driver_id = [])
    {
        // seharunys pilih yang paling dekat kemudian dicari yang on duty
        $search_area = setting('MAX_DRIVER_SEARCH_AREA_KM', 2);

        $near_driver_id = \App\Service\RedisService::nearDrivers(
            $pickup->lat,
            $pickup->long,
            $search_area
        );

        $driver = \App\RefDrivers::where([
            ["is_driver_active", 1],
            ["register_status", "approve"]
        ])
        ->whereRaw("on_duty_trx_order_id is null")
        ->whereIn("id", $near_driver_id);

        if (!empty($exclude_driver_id)) {
            $driver = $driver->whereNotIn("id", $exclude_driver_id);
        }
        
        $driver = $driver->first();

        return $driver;

    }

    public static function isAutoAccepted($id)
    {
        $driver = \App\RefDrivers::find($id);
        $driver_setting = \App\RefDriverSettings::where([
            ["setting_key", "autoaccept"],
            ["ref_drivers_id", $driver->id]
        ])->first();

        $autoaccept = 0;
        if (!empty($driver_setting)) {
            $autoaccept = $driver_setting->setting_value;
        }

        return $autoaccept;
    }
}