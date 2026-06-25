<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RBAC Improvements
    |--------------------------------------------------------------------------
    |
    | Master switch for phased RBAC hardening (Phases 1–4). When false, legacy
    | privilege behavior is unchanged. Enable per environment after testing.
    |
    */
    'enabled' => env('RBAC_IMPROVEMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Privilege name → slug (Phase 2 backfill)
    |--------------------------------------------------------------------------
    |
    | Maps cms_privileges.name to a stable slug during migration. Add entries
    | for roles created in admin UI so slugs stay consistent across environments.
    |
    */
    'name_slug_map' => [
        'Super Administrator' => 'super_admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy privilege ID → slug (Phase 2 backfill fallback)
    |--------------------------------------------------------------------------
    |
    | Used when name_slug_map has no match. Update these to match your DB if
    | privilege names differ between environments.
    |
    */
    'legacy_id_slug_map' => [
        1 => 'super_admin',
        5 => 'merchandiser',
        6 => 'store_manager',
        7 => 'approver',
        8 => 'viewer',
    ],

];
