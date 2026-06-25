<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoleResolver
{
    public static function resolvePrivilegeId($roleIdOrSlug): ?int
    {
        if ($roleIdOrSlug === null || $roleIdOrSlug === '') {
            return null;
        }

        if (is_numeric($roleIdOrSlug)) {
            return self::resolvePrivilegeIdById((int) $roleIdOrSlug);
        }

        return self::resolvePrivilegeIdBySlug(strtolower(trim((string) $roleIdOrSlug)));
    }

    public static function resolvePrivilegeIdBySlug(string $slug): ?int
    {
        if ($slug === '') {
            return null;
        }

        if (!Rbac::isEnabled()) {
            $id = DB::table('cms_privileges')->where('slug', $slug)->value('id');

            return $id ? (int) $id : null;
        }

        $id = Cache::remember('rbac.privilege.slug.' . $slug, 3600, function () use ($slug) {
            return DB::table('cms_privileges')->where('slug', $slug)->value('id');
        });

        return $id ? (int) $id : null;
    }

    public static function resolvePrivilegeIdById(int $id): ?int
    {
        if ($id <= 0) {
            return null;
        }

        if (!Rbac::isEnabled()) {
            return DB::table('cms_privileges')->where('id', $id)->exists() ? $id : null;
        }

        $exists = Cache::remember('rbac.privilege.id.' . $id, 3600, function () use ($id) {
            return DB::table('cms_privileges')->where('id', $id)->exists();
        });

        return $exists ? $id : null;
    }

    /**
     * @param string[] $slugs
     * @return int[]
     */
    public static function resolvePrivilegeIdsForSlugs(array $slugs): array
    {
        $ids = [];

        foreach ($slugs as $slug) {
            $id = self::resolvePrivilegeIdBySlug($slug);
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function slugForPrivilegeId($privilegeId): ?string
    {
        if (empty($privilegeId) || !is_numeric($privilegeId)) {
            return null;
        }

        $id = (int) $privilegeId;

        if (!Rbac::isEnabled()) {
            return DB::table('cms_privileges')->where('id', $id)->value('slug');
        }

        $slug = Cache::remember('rbac.privilege.id_slug.' . $id, 3600, function () use ($id) {
            return DB::table('cms_privileges')->where('id', $id)->value('slug');
        });

        return $slug ?: null;
    }

    public static function forgetCache(): void
    {
        if (!Rbac::isEnabled()) {
            return;
        }

        $privileges = DB::table('cms_privileges')->select('id', 'slug')->get();

        foreach ($privileges as $privilege) {
            Cache::forget('rbac.privilege.slug.' . $privilege->slug);
            Cache::forget('rbac.privilege.id.' . $privilege->id);
            Cache::forget('rbac.privilege.id_slug.' . $privilege->id);
        }
    }
}
