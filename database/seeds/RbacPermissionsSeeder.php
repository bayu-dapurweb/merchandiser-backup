<?php

use App\Services\RbacPermissionSync;
use Illuminate\Database\Seeder;

class RbacPermissionsSeeder extends Seeder
{
    public function run()
    {
        $stats = RbacPermissionSync::sync();

        if ($this->command) {
            $this->command->info('RBAC permissions seeded.');
            $this->command->line('Permissions processed: ' . $stats['permissions']);
            $this->command->line('New role-permission assignments: ' . $stats['assignments']);
        }
    }
}
