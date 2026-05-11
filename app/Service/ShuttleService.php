<?php 
namespace App\Service;

use Illuminate\Support\Facades\Log;

class ShuttleService
{
    public static function isSeatAvailable(
        $departure_at,
        $schedule_id,
        $seat_id
    ) {
        $departure_at = slug($departure_at);
        $schedule_id = slug($schedule_id);
        $is_booked_seat = \App\TrxShuttleOrdersItems::where([
            ["ref_shuttle_schedules_seats_id", $seat_id]
        ])->whereRaw("trx_shuttle_orders_id IN (
            SELECT id 
            FROM trx_shuttle_orders
            WHERE deleted_at is null
            AND expired_at > now()
            AND order_status IN ('draft', 'paid')
            AND departure_date = '$departure_at'
            AND ref_shuttle_schedules_id = $schedule_id
        )")->first();
        if (!empty($is_booked_seat)) {
            return false;
        } else {
            return true;
        }
    }

    public static function selectedSeats($user_id)
    {
        $seats = \App\TrxShuttleOrdersItems::whereRaw("trx_shuttle_orders_id IN (
            SELECT id 
            FROM trx_shuttle_orders
            WHERE deleted_at is null
            AND ref_users_id = '$user_id'
            AND expired_at > now()
            AND order_status IN ('draft', 'paid')
        )")->get()->pluck('ref_shuttle_schedules_seats_id');

        if (!empty($seats)) {
            return $seats->toArray();
        } else {
            return [];
        }
    }
}