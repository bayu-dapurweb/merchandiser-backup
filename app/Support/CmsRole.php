<?php

namespace App\Support;

/**
 * Stable role identifiers (cms_privileges.slug).
 *
 * Used by Phases 2–4 instead of hardcoded numeric privilege IDs.
 * Slugs are backfilled via migration/config; adjust config/rbac.php maps
 * if your environment uses different privilege names.
 */
final class CmsRole
{
    public const SUPER_ADMIN = 'super_admin';
    public const APPROVER = 'approver';
    public const MERCHANDISER = 'merchandiser';
    public const STORE_MANAGER = 'store_manager';
    public const VIEWER = 'viewer';

    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::APPROVER,
            self::MERCHANDISER,
            self::STORE_MANAGER,
            self::VIEWER,
        ];
    }
}
