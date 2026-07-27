<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsPermissionsTables extends Migration
{
    public function up()
    {
        Schema::create('cms_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('module', 100)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_privilege_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_cms_privileges');
            $table->unsignedInteger('id_cms_permissions');
            $table->timestamps();

            $table->unique(['id_cms_privileges', 'id_cms_permissions'], 'cms_privilege_permissions_unique');
            $table->index('id_cms_privileges');
            $table->index('id_cms_permissions');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cms_privilege_permissions');
        Schema::dropIfExists('cms_permissions');
    }
}
