<?php

namespace App\Services;

use App\CmsUsers;
use App\Support\CmsRole;
use App\Support\Rbac;

class AuthorizationService
{
    /**
     * Check whether a user belongs to a configured role group.
     *
     * When RBAC is disabled, falls back to legacy numeric privilege IDs
     * derived from config('rbac.legacy_id_slug_map').
     */
    public static function allows($user, string $group): bool
    {
        $roles = config("rbac.role_groups.{$group}");

        if (!is_array($roles) || empty($roles)) {
            return false;
        }

        return self::userHasAnyRole($user, $roles);
    }

    public static function denies($user, string $group): bool
    {
        return !self::allows($user, $group);
    }

    public static function isSuperAdmin($user): bool
    {
        return self::allows($user, 'super_admin_only');
    }

    public static function isMerchandiser($user): bool
    {
        return self::allows($user, 'merchandiser_only');
    }

    public static function userHasRole($user, string $roleSlug): bool
    {
        return self::userHasAnyRole($user, [$roleSlug]);
    }

    /**
     * @param string[] $roleSlugs
     */
    public static function userHasAnyRole($user, array $roleSlugs): bool
    {
        if (!$user || empty($user->id_cms_privileges)) {
            return false;
        }

        if (!Rbac::isEnabled()) {
            $legacyIds = self::legacyIdsForRoleSlugs($roleSlugs);

            return in_array((int) $user->id_cms_privileges, $legacyIds, true);
        }

        $cmsUser = self::resolveCmsUser($user);

        return $cmsUser ? $cmsUser->hasAnyRole($roleSlugs) : false;
    }

    /**
     * @param string[] $roleSlugs
     * @return int[]
     */
    public static function legacyIdsForRoleSlugs(array $roleSlugs): array
    {
        $map = config('rbac.legacy_id_slug_map', []);
        $ids = [];

        foreach ($map as $id => $slug) {
            if (in_array($slug, $roleSlugs, true)) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private static function resolveCmsUser($user): ?CmsUsers
    {
        if ($user instanceof CmsUsers) {
            if (!$user->relationLoaded('privilege')) {
                $user->load('privilege');
            }

            return $user;
        }

        if (!empty($user->id)) {
            return CmsUsers::with('privilege')->find($user->id);
        }

        return null;
    }
}
