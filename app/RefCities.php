<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefCities extends Model
{
    protected $casts = [
        "province_id" => "integer"
    ];
}
