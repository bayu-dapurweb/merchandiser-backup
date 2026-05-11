<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CmsUsers extends Model
{
    public function stores()
    {
        return $this->hasMany(MapCmsUsersRefWarehouse::class, "cms_users_id");
    }

    public function branches()
    {
        return $this->hasMany(MapCmsUsersRefBranches::class, "cms_users_id");
    }
}
