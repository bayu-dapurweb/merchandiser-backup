<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MapCmsUsersRefWarehouse extends Model
{
    public function store()
    {
        return $this->belongsTo(RefWarehouses::class, "ref_warehouses_id");
    }
}
