<?php

namespace App\Services;

use App\CmsUsers;
use App\Support\CmsRole;
use App\Support\ModulePrivilege;
use App\Support\PermissionResolver;
use App\Support\Rbac;

class AuthorizationService
{
    /**
     * Check whether a user has a named permission.
     */
    public static function allows($user, string $permission): bool
    {
        if (!$user || empty($user->id_cms_privileges)) {
            return false;
        }

        $approvePath = array_search($permission, config('rbac.approve_legacy_permission_map', []), true);
        if ($approvePath !== false) {
            return ModulePrivilege::canApprove($user, $approvePath);
        }

        if (!Rbac::isEnabled()) {
            return self::allowsViaConfigRoleGroups($user, $permission, true);
        }

        $cmsUser = self::resolveCmsUser($user);
        if ($cmsUser && $cmsUser->isSuperAdmin()) {
            return true;
        }

        if (Rbac::usesDatabasePermissions()) {
            return PermissionResolver::privilegeHasPermission((int) $user->id_cms_privileges, $permission);
        }

        return self::allowsViaConfigRoleGroups($user, $permission, false);
    }

    public static function denies($user, string $permission): bool
    {
        return !self::allows($user, $permission);
    }

    public static function can($user, string $permission): bool
    {
        return self::allows($user, $permission);
    }

    public static function isSuperAdmin($user): bool
    {
        if (!$user || empty($user->id_cms_privileges)) {
            return false;
        }

        if (!Rbac::isEnabled()) {
            return in_array((int) $user->id_cms_privileges, self::legacyIdsForRoleSlugs([CmsRole::SUPER_ADMIN]), true);
        }

        $cmsUser = self::resolveCmsUser($user);

        return $cmsUser ? $cmsUser->isSuperAdmin() : false;
    }

    public static function isMerchandiser($user): bool
    {
        return self::userHasAnyRole($user, [CmsRole::MERCHANDISER]);
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

    public static function allowsViaConfigRoleGroups($user, string $permission, bool $legacyMode): bool
    {
        $roles = config("rbac.role_groups.{$permission}");

        if (!is_array($roles) || empty($roles)) {
            return false;
        }

        if ($legacyMode) {
            $legacyIds = self::legacyIdsForRoleSlugs($roles);

            return in_array((int) $user->id_cms_privileges, $legacyIds, true);
        }

        return self::userHasAnyRole($user, $roles);
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
