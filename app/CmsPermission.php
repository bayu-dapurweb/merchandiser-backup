<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CmsPermission extends Model
{
    protected $table = 'cms_permissions';

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    public function privileges()
    {
        return $this->belongsToMany(
            CmsPrivilege::class,
            'cms_privilege_permissions',
            'id_cms_permissions',
            'id_cms_privileges'
        )->withTimestamps();
    }
}
