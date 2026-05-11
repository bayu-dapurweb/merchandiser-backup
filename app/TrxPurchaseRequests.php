<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxPurchaseRequests extends Model
{
    use SoftDeletes;
    
    public function items()
    {
        return $this->hasMany(TrxPurchaseRequestsItems::class, 'trx_purchase_requests_id');
    }
}
