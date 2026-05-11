<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxGoodsReceipts extends Model
{
    use SoftDeletes;

    public function items()
    {
        return $this->hasMany(TrxGoodsReceiptItems::class, 'trx_goods_receipts_id');
    }
}
