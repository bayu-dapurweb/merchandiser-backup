<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxGoodsReturns extends Model
{
    use SoftDeletes;

    public function items()
    {
        return $this->hasMany(TrxGoodsReturnItems::class, 'trx_goods_returns_id');
    }
}
