<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapCmsUsersRefWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_cms_users_ref_warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ref_warehouses_id')->nullable();
            $table->integer('cms_users_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map_cms_users_ref_warehouses');
    }
}
