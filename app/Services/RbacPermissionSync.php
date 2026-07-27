<?php

namespace App\Services;

use App\Support\PermissionResolver;
use App\Support\RoleResolver;
use Illuminate\Support\Facades\DB;

class RbacPermissionSync
{
    /**
     * Seed/sync cms_permissions and cms_privilege_permissions from config.
     *
     * @return array{permissions:int,assignments:int}
     */
    public static function sync(): array
    {
        $roleGroups = config('rbac.role_groups', []);
        $stats = ['permissions' => 0, 'assignments' => 0];
        $now = date('Y-m-d H:i:s');

        DB::transaction(function () use ($roleGroups, $now, &$stats) {
            $permissionIdsBySlug = [];

            foreach ($roleGroups as $permissionSlug => $roleSlugs) {
                $meta = config("rbac.permissions.{$permissionSlug}", []);
                $permissionId = self::upsertPermission($permissionSlug, $meta, $now);
                $permissionIdsBySlug[$permissionSlug] = $permissionId;
                $stats['permissions']++;

                foreach ((array) $roleSlugs as $roleSlug) {
                    $privilegeId = RoleResolver::resolvePrivilegeIdBySlug($roleSlug);
                    if ($privilegeId === null) {
                        continue;
                    }

                    if (self::attachPermission($privilegeId, $permissionId, $now)) {
                        $stats['assignments']++;
                    }
                }
            }

            self::grantAllPermissionsToSuperAdmins($permissionIdsBySlug, $now, $stats);
        });

        PermissionResolver::forgetCache();
        RoleResolver::forgetCache();

        return $stats;
    }

    private static function upsertPermission(string $slug, array $meta, string $now): int
    {
        $name = $meta['name'] ?? ucwords(str_replace('_', ' ', $slug));
        $module = $meta['module'] ?? null;
        $description = $meta['description'] ?? null;

        $existing = DB::table('cms_permissions')->where('slug', $slug)->first();

        if ($existing) {
            DB::table('cms_permissions')->where('id', $existing->id)->update([
                'name' => $name,
                'module' => $module,
                'description' => $description,
                'updated_at' => $now,
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('cms_permissions')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'module' => $module,
            'description' => $description,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private static function attachPermission(int $privilegeId, int $permissionId, string $now): bool
    {
        $exists = DB::table('cms_privilege_permissions')
            ->where('id_cms_privileges', $privilegeId)
            ->where('id_cms_permissions', $permissionId)
            ->exists();

        if ($exists) {
            return false;
        }

        DB::table('cms_privilege_permissions')->insert([
            'id_cms_privileges' => $privilegeId,
            'id_cms_permissions' => $permissionId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }

    /**
     * @param array<string,int> $permissionIdsBySlug
     */
    private static function grantAllPermissionsToSuperAdmins(array $permissionIdsBySlug, string $now, array &$stats): void
    {
        $excludedSlugs = ['merchandiser_only'];

        $superAdminPrivilegeIds = DB::table('cms_privileges')
            ->where('slug', 'super_admin')
            ->orWhere('is_superadmin', 1)
            ->pluck('id')
            ->unique()
            ->all();

        foreach ($superAdminPrivilegeIds as $privilegeId) {
            foreach ($permissionIdsBySlug as $slug => $permissionId) {
                if (in_array($slug, $excludedSlugs, true)) {
                    continue;
                }

                if (self::attachPermission((int) $privilegeId, (int) $permissionId, $now)) {
                    $stats['assignments']++;
                }
            }
        }
    }
}
