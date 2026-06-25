<?php

namespace App\Support;

use CRUDBooster;
use DB;

class Rbac
{
    public static function isEnabled(): bool
    {
        return filter_var(config('rbac.enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function isSuperAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user instanceof \App\CmsUsers) {
            return $user->isSuperAdmin();
        }

        if (empty($user->id_cms_privileges)) {
            return false;
        }

        if (self::isEnabled()) {
            $slug = RoleResolver::slugForPrivilegeId($user->id_cms_privileges);
            if ($slug === CmsRole::SUPER_ADMIN) {
                return true;
            }
        }

        $privilege = DB::table('cms_privileges')->where('id', $user->id_cms_privileges)->first();

        return $privilege && !empty($privilege->is_superadmin);
    }

    /**
     * Phase 1: hide role field in forms when the actor cannot assign roles.
     *
     * @param int|null $targetUserId Omit on add; pass user id on edit.
     */
    public static function shouldHideRoleField($targetUserId = null): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $actor = \CB::me();

        if ($targetUserId === null) {
            return !self::isSuperAdmin($actor);
        }

        $isSelfEdit = (int) $targetUserId === (int) CRUDBooster::myId();

        return $isSelfEdit || !self::isSuperAdmin($actor);
    }

    /**
     * Phase 1: prevent privilege escalation on user edit.
     */
    public static function guardUserPrivilegeOnEdit(array &$postdata, $targetUserId, $actor): void
    {
        if (!self::isEnabled() || !array_key_exists('id_cms_privileges', $postdata)) {
            return;
        }

        $isSelfEdit = (int) $targetUserId === (int) CRUDBooster::myId();

        if ($isSelfEdit || !self::isSuperAdmin($actor)) {
            unset($postdata['id_cms_privileges']);
            return;
        }

        self::validatePrivilegeAssignmentOrRedirect(
            $postdata['id_cms_privileges'],
            CRUDBooster::mainpath('edit/' . $targetUserId)
        );
    }

    /**
     * Phase 1: only super admins may assign roles when creating users.
     */
    public static function guardUserPrivilegeOnAdd(array &$postdata, $actor): void
    {
        if (!self::isEnabled()) {
            return;
        }

        if (!array_key_exists('id_cms_privileges', $postdata)) {
            return;
        }

        if (!self::isSuperAdmin($actor)) {
            unset($postdata['id_cms_privileges']);
            CRUDBooster::redirect(
                CRUDBooster::mainpath('add'),
                'You are not authorized to assign user roles.',
                'danger'
            );
        }

        self::validatePrivilegeAssignmentOrRedirect($postdata['id_cms_privileges'], CRUDBooster::mainpath('add'));
    }

    /**
     * Phase 1/2: validate CSV role by numeric ID or slug.
     *
     * @return int|null Valid privilege ID, or null when RBAC is enabled and value is invalid.
     */
    public static function resolveImportPrivilegeId($roleIdOrSlug)
    {
        if (!self::isEnabled()) {
            return $roleIdOrSlug;
        }

        return RoleResolver::resolvePrivilegeId($roleIdOrSlug);
    }

    /**
     * Phase 2: resolve stable role slugs to privilege IDs for authorization checks.
     *
     * @param string[] $slugs
     * @return int[]
     */
    public static function privilegeIdsForRoles(array $slugs): array
    {
        if (!self::isEnabled()) {
            return [];
        }

        return RoleResolver::resolvePrivilegeIdsForSlugs($slugs);
    }

    private static function validatePrivilegeAssignmentOrRedirect($privilegeIdOrSlug, string $redirectPath): void
    {
        if (RoleResolver::resolvePrivilegeId($privilegeIdOrSlug) === null) {
            CRUDBooster::redirect($redirectPath, 'Invalid privilege selected.', 'danger');
        }
    }
}
