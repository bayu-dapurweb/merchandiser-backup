<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefKecamatans extends Model
{
    protected $casts = [
        "province_id" => "integer",
        "city_id" => "integer",
    ];
}
