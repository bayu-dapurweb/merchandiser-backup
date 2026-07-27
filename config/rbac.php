<?php

use App\Support\CmsRole;

return [

    'enabled' => env('RBAC_IMPROVEMENTS_ENABLED', false),

    'use_database_permissions' => env('RBAC_USE_DATABASE_PERMISSIONS', true),

    'name_slug_map' => [
        'Super Administrator' => 'super_admin',
    ],

    'legacy_id_slug_map' => [
        1 => 'super_admin',
        5 => 'merchandiser',
        6 => 'store_manager',
        7 => 'approver',
        8 => 'viewer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module paths that show an Approve column on the Privileges screen
    |--------------------------------------------------------------------------
    */
    'approve_module_paths' => [
        'trx_purchase_orders',
        'trx_purchase_requests',
        'trx_goods_receipts',
        'trx_goods_returns',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy approve permission slugs (RBAC off / fallback)
    |--------------------------------------------------------------------------
    */
    'approve_legacy_permission_map' => [
        'trx_purchase_orders' => 'purchase_order_approve',
        'trx_purchase_requests' => 'purchase_request_approve',
        'trx_goods_receipts' => 'goods_receipt_approve',
        'trx_goods_returns' => 'goods_return_approve',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role groups (legacy fallback when RBAC_IMPROVEMENTS_ENABLED=false)
    |--------------------------------------------------------------------------
    */
    'role_groups' => [
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
        'goods_return_approve' => [
            CmsRole::SUPER_ADMIN,
            CmsRole::STORE_MANAGER,
            CmsRole::APPROVER,
        ],
    ],

];
