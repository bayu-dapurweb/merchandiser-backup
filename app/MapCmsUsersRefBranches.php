<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MapCmsUsersRefBranches extends Model
{
    public function branch()
    {
        return $this->belongsTo(RefBranches::class, "ref_branches_id");
    }
}
