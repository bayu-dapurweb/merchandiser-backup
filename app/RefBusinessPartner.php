<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefBusinessPartner extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'type',
        'vatcode',
    ];
}
