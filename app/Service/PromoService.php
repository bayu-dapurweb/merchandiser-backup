<?php 
namespace App\Service;

class PromoService
{
    public static function index()
    {
        $promos = \App\RefPromos::with("media");
        if (get('search')) {
            $search = get('search');
            $search = alphanumeric_only($search);
            $promos = $promos->whereRaw("title like '%$search%'");
        }
        if (get('is_available_only', 0)  == 1) {
            $now = date("Y-m-d H:i:s");
            
            if (get('code')) {
                $promos->whereRaw("(promo_group = 'porter' OR (start_at <= '$now' and end_at >= '$now'))");
            } else {
                $promos->whereRaw("(promo_group != 'porter' OR (start_at <= '$now' and end_at >= '$now'))");
            }
            //harusnya nanti ditambah checking penggunaan
        }
        if (get('order_type') == "direct") {
            $promos->where("is_available_for_direct_trip", 1);
        }

        if (get('order_type') == "later") {
            $promos->where("is_available_for_later_trip", 1);
        }

        if (get('order_type') == "rental") {
            $promos->where("is_available_for_rental", 1);
        }
        
        if (get('code')) {
            $promos = $promos->where("voucher_code", get('code'));
        } else {
            $promos = $promos->where("is_publish", 1);
        }
        
        $promos = $promos->paginate(get('limit', 10));
        $data = paginationExplode($promos);

        /* check if this is a porter promo */
        foreach ($data['data'] as $k => $v) {
            if ($v['promo_group'] == "porter") {
                $data['data'][$k]["title"] = $data['data'][$k]["title"] . ", Porter Discount Rp" . nominal(setting("PORTER_PROMO_DISCOUNT_FOR_USER", 1000));
                $data['data'][$k]["start_at"] = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " -1 hours"));
                $data['data'][$k]["end_at"] = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " +1 hours"));
                $data['data'][$k]["media"] = [
                    "id" => "0",
                    "url" => uri("assets/elvista/promo-porter.jpg")
                ];
            }
        }
        
        return $data;
    }

    public static function find($id)
    {
        $promos = \App\RefPromos::with("media")->find($id);
        return $promos;
    }

    public static function availabilitycheck($promo_id, $order_id)
    {
        $order = \App\TrxOrders::find($order_id);
        $promo = \App\RefPromos::find($promo_id);

        /* if porter promo, skip the check */
        if ($promo->promo_group == "porter") {
            return [];
        }

        if ($promo->is_available_for_direct_trip == 0 && $order->order_type == "direct")  {
            return [
                'code' => 422,
                'message' => 'The promo is not available for direct trip',
            ];
        }

        if ($promo->is_available_for_later_trip == 0 && $order->order_type == "later")  {
            return [
                'code' => 422,
                'message' => 'The promo is not available for scheduled trip',
            ];
        }

        if ($promo->is_available_for_rental == 0 && $order->order_type == "rental")  {
            return [
                'code' => 422,
                'message' => 'The promo is not available for rental',
            ];
        }

        //minimum transaction
        if ($promo->min_transaction_mount >= $order->basic_price) {
            return [
                'code' => 422,
                'message' => 'Minimum order for the promo is Rp' . nominal($promo->min_transaction_mount) . ", your order is Rp" . nominal($order->basic_price),
            ];
        }

        //max_buget
        $redeem = \App\TrxOrdersPromosRedeems::selectRaw("sum(redeemed_amount) as total")
            ->where("ref_promos_id", $promo_id)
            ->first();
        
        if (!empty($redeem)) {
            if ($redeem->total >= $promo->max_buget) {
                return [
                    'code' => 422,
                    'message' => 'The promo has reacheded maximum expenses',
                ];
            }
        }

        //max_redeem
        $redeem = \App\TrxOrdersPromosRedeems::selectRaw("count(*) as total")
            ->where("ref_promos_id", $promo_id)
            ->first();
        
        if (!empty($redeem)) {
            if ($redeem->total >= $promo->max_redeem) {
                return [
                    'code' => 422,
                    'message' => 'The promo have reached maximum usage',
                ];
            }
        }
        
        //max redeem user
        $redeem = \App\TrxOrdersPromosRedeems::selectRaw("count(*) as total")
            ->where("ref_promos_id", $promo_id)
            ->where("ref_users_id", $order->ref_users_id)
            ->first();
        
        if (!empty($redeem)) {
            if ($redeem->total >= $promo->max_redeem_user) {
                return [
                    'code' => 422,
                    'message' => 'The promo has reached maximum usage for your account',
                ];
            }
        }

        //max redeem user to day
        $redeem = \App\TrxOrdersPromosRedeems::selectRaw("count(*) as total")
            ->where("ref_promos_id", $promo_id)
            ->where("ref_users_id", $order->ref_users_id)
            ->whereRaw("date(created_at)", date("Y-m-d"))
            ->first();
        
        if (!empty($redeem)) {
            if ($redeem->total >= $promo->max_redeem_user_day) {
                return [
                    'code' => 422,
                    'message' => 'The promo has reached maximum usage for your account today, please try again tomorow',
                ];
            }
        }
    }
}