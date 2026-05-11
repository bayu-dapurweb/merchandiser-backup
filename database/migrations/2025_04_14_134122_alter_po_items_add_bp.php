<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPoItemsAddBp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_purchase_orders_items', function (Blueprint $table) {
            $table->integer('ref_business_partners_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_purchase_orders_items', function (Blueprint $table) {
            $table->dropColumn('ref_business_partners_id');
        });
    }
}
