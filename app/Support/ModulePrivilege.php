<?php

namespace App\Support;

use App\Services\AuthorizationService;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModulePrivilege
{
    public static function approveModulePaths(): array
    {
        return config('rbac.approve_module_paths', []);
    }

    public static function supportsApprove(?string $modulePath): bool
    {
        return $modulePath && in_array($modulePath, self::approveModulePaths(), true);
    }

    public static function canApprove($user = null, ?string $modulePath = null): bool
    {
        if (CRUDBooster::isSuperadmin()) {
            return true;
        }

        $path = $modulePath ?? CRUDBooster::getModulePath();
        if (!self::supportsApprove($path)) {
            return false;
        }

        $privilegeId = (int) ($user->id_cms_privileges ?? CRUDBooster::myPrivilegeId());
        if ($privilegeId <= 0) {
            return false;
        }

        if (Rbac::isEnabled() && Schema::hasColumn('cms_privileges_roles', 'is_approve')) {
            return DB::table('cms_privileges_roles')
                ->join('cms_moduls', 'cms_moduls.id', '=', 'cms_privileges_roles.id_cms_moduls')
                ->where('cms_privileges_roles.id_cms_privileges', $privilegeId)
                ->where('cms_moduls.path', $path)
                ->where('cms_privileges_roles.is_approve', 1)
                ->exists();
        }

        return self::legacyCanApprove($user, $path);
    }

    public static function deniesApprove($user = null, ?string $modulePath = null): bool
    {
        return !self::canApprove($user, $modulePath);
    }

    public static function sessionRoleColumns(): array
    {
        return [
            'cms_moduls.name',
            'cms_moduls.path',
            'is_visible',
            'is_create',
            'is_read',
            'is_edit',
            'is_delete',
            'is_approve',
        ];
    }

    private static function legacyCanApprove($user, string $modulePath): bool
    {
        $slug = config("rbac.approve_legacy_permission_map.{$modulePath}");
        if (!$slug) {
            return false;
        }

        return AuthorizationService::allowsViaConfigRoleGroups($user, $slug, !Rbac::isEnabled());
    }
}
