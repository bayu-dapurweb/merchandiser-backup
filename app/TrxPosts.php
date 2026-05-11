<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxPosts extends Model
{
    use SoftDeletes;

    public function meta()
    {
        return json_decode($this->meta);
    }
}
