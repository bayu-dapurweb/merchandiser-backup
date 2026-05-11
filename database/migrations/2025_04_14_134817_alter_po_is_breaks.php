<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPoIsBreaks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('trx_purchase_orders', function (Blueprint $table) {
            $table->integer('is_breaked')->nullable()->default(0);
            $table->integer('parent_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('is_breaked');
            $table->dropColumn('parent_id');
            
        });
    }
}
