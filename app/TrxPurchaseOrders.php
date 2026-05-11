<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxPurchaseOrders extends Model
{
    use SoftDeletes;
    
    public function items()
    {
        return $this->hasMany(TrxPurchaseOrdersItems::class, "trx_purchase_orders_id");
    }
}
