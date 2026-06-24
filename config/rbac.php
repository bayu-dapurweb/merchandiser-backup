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

];
