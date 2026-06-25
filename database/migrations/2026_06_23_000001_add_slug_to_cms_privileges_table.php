<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSlugToCmsPrivilegesTable extends Migration
{
    public function up()
    {
        Schema::table('cms_privileges', function (Blueprint $table) {
            $table->string('slug', 50)->nullable()->after('name');
        });

        $this->backfillSlugs();

        Schema::table('cms_privileges', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::table('cms_privileges', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    private function backfillSlugs()
    {
        $nameSlugMap = config('rbac.name_slug_map', []);
        $legacyIdSlugMap = config('rbac.legacy_id_slug_map', []);
        $usedSlugs = [];
        $privileges = DB::table('cms_privileges')->orderBy('id')->get();

        foreach ($privileges as $privilege) {
            $slug = null;

            if (!empty($privilege->name) && isset($nameSlugMap[$privilege->name])) {
                $slug = $nameSlugMap[$privilege->name];
            } elseif (isset($legacyIdSlugMap[(int) $privilege->id])) {
                $slug = $legacyIdSlugMap[(int) $privilege->id];
            } elseif (!empty($privilege->name)) {
                $slug = str_slug($privilege->name, '_');
            }

            if (empty($slug)) {
                $slug = 'role_' . $privilege->id;
            }

            $slug = $this->uniqueSlug($slug, $usedSlugs, $privilege->id);
            $usedSlugs[] = $slug;

            DB::table('cms_privileges')->where('id', $privilege->id)->update([
                'slug' => $slug,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function uniqueSlug($base, array $usedSlugs, $id)
    {
        $slug = strtolower($base);
        $candidate = $slug;
        $suffix = 2;

        while (in_array($candidate, $usedSlugs, true)) {
            $candidate = $slug . '_' . $suffix;
            $suffix++;
        }

        if ($candidate === '' || $candidate === '_') {
            return 'role_' . $id;
        }

        return $candidate;
    }
}
