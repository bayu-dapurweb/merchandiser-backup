<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixPrivilegesModuleRouting extends Migration
{
    public function up()
    {
        DB::table('cms_moduls')
            ->where('table_name', 'cms_privileges')
            ->update([
                'controller' => 'AdminPrivilegesController',
                'is_protected' => 0,
            ]);
    }

    public function down()
    {
        //
    }
}
