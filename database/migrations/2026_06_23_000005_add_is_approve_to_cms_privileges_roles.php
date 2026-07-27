<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsApproveToCmsPrivilegesRoles extends Migration
{
    public function up()
    {
        Schema::table('cms_privileges_roles', function (Blueprint $table) {
            $table->boolean('is_approve')->nullable()->after('is_delete');
        });

        $this->backfillApproveFromBusinessPermissions();
    }

    public function down()
    {
        Schema::table('cms_privileges_roles', function (Blueprint $table) {
            $table->dropColumn('is_approve');
        });
    }

    private function backfillApproveFromBusinessPermissions(): void
    {
        if (!Schema::hasTable('cms_permissions') || !Schema::hasTable('cms_privilege_permissions')) {
            return;
        }

        $slugToPath = array_flip(config('rbac.approve_legacy_permission_map', []));

        $assignments = DB::table('cms_privilege_permissions')
            ->join('cms_permissions', 'cms_permissions.id', '=', 'cms_privilege_permissions.id_cms_permissions')
            ->select('cms_privilege_permissions.id_cms_privileges', 'cms_permissions.slug')
            ->get();

        foreach ($assignments as $row) {
            $path = $slugToPath[$row->slug] ?? null;
            if (!$path) {
                continue;
            }

            $moduleId = DB::table('cms_moduls')->where('path', $path)->value('id');
            if (!$moduleId) {
                continue;
            }

            DB::table('cms_privileges_roles')
                ->where('id_cms_privileges', $row->id_cms_privileges)
                ->where('id_cms_moduls', $moduleId)
                ->update(['is_approve' => 1]);
        }
    }
}
