<?php

namespace App\Console\Commands;

use App\Services\RbacPermissionSync;
use Illuminate\Console\Command;

class RbacSyncPermissions extends Command
{
    protected $signature = 'rbac:sync-permissions';

    protected $description = 'Sync cms_permissions and role-permission mappings from config/rbac.php';

    public function handle()
    {
        $stats = RbacPermissionSync::sync();

        $this->info('RBAC permissions synced.');
        $this->line('Permissions processed: ' . $stats['permissions']);
        $this->line('New role-permission assignments: ' . $stats['assignments']);

        return 0;
    }
}
