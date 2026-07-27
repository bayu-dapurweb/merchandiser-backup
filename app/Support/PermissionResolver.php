<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionResolver
{
    public static function privilegeHasPermission(int $privilegeId, string $permissionSlug): bool
    {
        if ($privilegeId <= 0 || $permissionSlug === '') {
            return false;
        }

        if (!Schema::hasTable('cms_permissions') || !Schema::hasTable('cms_privilege_permissions')) {
            return false;
        }

        $permissionSlug = strtolower(trim($permissionSlug));
        $cacheKey = 'rbac.privilege.' . $privilegeId . '.permission.' . $permissionSlug;

        return (bool) Cache::remember($cacheKey, 3600, function () use ($privilegeId, $permissionSlug) {
            return DB::table('cms_privilege_permissions')
                ->join(
                    'cms_permissions',
                    'cms_permissions.id',
                    '=',
                    'cms_privilege_permissions.id_cms_permissions'
                )
                ->where('cms_privilege_permissions.id_cms_privileges', $privilegeId)
                ->where('cms_permissions.slug', $permissionSlug)
                ->exists();
        });
    }

    public static function forgetCache(): void
    {
        if (!Schema::hasTable('cms_permissions') || !Schema::hasTable('cms_privileges')) {
            return;
        }

        $privileges = DB::table('cms_privileges')->pluck('id');
        $permissions = DB::table('cms_permissions')->pluck('slug');

        foreach ($privileges as $privilegeId) {
            foreach ($permissions as $permissionSlug) {
                Cache::forget('rbac.privilege.' . $privilegeId . '.permission.' . $permissionSlug);
            }
        }
    }
}
