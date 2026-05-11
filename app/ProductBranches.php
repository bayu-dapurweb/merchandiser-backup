<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBranches extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ref_branches_id',
        'ref_item_master_datas_id'
    ];
}
