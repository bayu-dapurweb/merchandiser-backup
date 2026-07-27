<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CmsPrivilege extends Model
{
    protected $table = 'cms_privileges';

    protected $fillable = [
        'name',
        'slug',
        'is_superadmin',
        'theme_color',
    ];

    public function users()
    {
        return $this->hasMany(CmsUsers::class, 'id_cms_privileges');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            CmsPermission::class,
            'cms_privilege_permissions',
            'id_cms_privileges',
            'id_cms_permissions'
        )->withTimestamps();
    }
}
