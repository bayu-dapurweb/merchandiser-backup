<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseOrderItemsAddPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('trx_purchase_orders_items', function (Blueprint $table) {
            $table->integer('ref_purchase_price_lists_id')->nullable();
            $table->float('PriceBefDi')->nullable();
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
            $table->dropColumn('ref_purchase_price_lists_id');
            $table->dropColumn('PriceBefDi');
        });
    }
}
