<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefKelurahans extends Model
{
    protected $casts = [
        "province_id" => "integer",
        "city_id" => "integer",
        "kecamatan_id" => "integer",
    ];
}
