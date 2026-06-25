<?php

use App\Support\CmsRole;

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

    /*
    |--------------------------------------------------------------------------
    | Role groups (Phase 3 authorization)
    |--------------------------------------------------------------------------
    |
    | Named groups replace hardcoded privilege ID arrays in controllers.
    | Keys are stable slugs from cms_privileges.slug (see CmsRole constants).
    |
    */
    'role_groups' => [
        'super_admin_only' => [
            CmsRole::SUPER_ADMIN,
        ],
        'merchandiser_only' => [
            CmsRole::MERCHANDISER,
        ],
        'transaction_crud' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::STORE_MANAGER,
            CmsRole::APPROVER,
            CmsRole::VIEWER,
        ],
        'transaction_view' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::STORE_MANAGER,
            CmsRole::APPROVER,
            CmsRole::VIEWER,
        ],
        'purchase_order_create' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
        ],
        'purchase_order_edit' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
        ],
        'purchase_order_delete' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
        ],
        'purchase_order_approve' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
        ],
        'purchase_request_approve' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
        ],
        'goods_receipt_approve' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
            CmsRole::STORE_MANAGER,
        ],
        'goods_receipt_approve_strict' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::APPROVER,
        ],
        'goods_return_approve' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::STORE_MANAGER,
            CmsRole::APPROVER,
        ],
    ],

];
