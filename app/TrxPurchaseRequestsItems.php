<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TrxPurchaseRequestsItems extends Model
{
    // use SoftDeletes;

    public function item()
    {
        return $this->belongsTo(RefItemMasterData::class, 'ref_item_master_datas_id');
    }
}
