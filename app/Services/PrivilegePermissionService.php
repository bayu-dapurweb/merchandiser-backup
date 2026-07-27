<?php

namespace App\Services;

use App\Support\PermissionResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrivilegePermissionService
{
    /**
     * @return string[]
     */
    public static function assignedSlugsForPrivilege(int $privilegeId): array
    {
        if ($privilegeId <= 0 || !Schema::hasTable('cms_privilege_permissions')) {
            return [];
        }

        return DB::table('cms_privilege_permissions')
            ->join(
                'cms_permissions',
                'cms_permissions.id',
                '=',
                'cms_privilege_permissions.id_cms_permissions'
            )
            ->where('cms_privilege_permissions.id_cms_privileges', $privilegeId)
            ->pluck('cms_permissions.slug')
            ->all();
    }

    /**
     * @param string[] $permissionSlugs
     */
    public static function syncForPrivilege(int $privilegeId, array $permissionSlugs): void
    {
        if ($privilegeId <= 0 || !Schema::hasTable('cms_privilege_permissions')) {
            return;
        }

        DB::table('cms_privilege_permissions')->where('id_cms_privileges', $privilegeId)->delete();

        $permissionSlugs = array_values(array_unique(array_filter(array_map('strval', $permissionSlugs))));
        if (empty($permissionSlugs)) {
            PermissionResolver::forgetCache();
            return;
        }

        $now = date('Y-m-d H:i:s');
        $permissionIds = DB::table('cms_permissions')
            ->whereIn('slug', $permissionSlugs)
            ->pluck('id', 'slug');

        foreach ($permissionSlugs as $slug) {
            if (!isset($permissionIds[$slug])) {
                continue;
            }

            DB::table('cms_privilege_permissions')->insert([
                'id_cms_privileges' => $privilegeId,
                'id_cms_permissions' => $permissionIds[$slug],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        PermissionResolver::forgetCache();
    }

    public static function deleteForPrivilege(int $privilegeId): void
    {
        if ($privilegeId <= 0 || !Schema::hasTable('cms_privilege_permissions')) {
            return;
        }

        DB::table('cms_privilege_permissions')->where('id_cms_privileges', $privilegeId)->delete();
        PermissionResolver::forgetCache();
    }
}
