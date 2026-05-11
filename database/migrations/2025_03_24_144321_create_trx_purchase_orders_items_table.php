<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxPurchaseOrdersItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_purchase_orders_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trx_purchase_orders')->nullable();
            $table->string('ItemCode')->nullable();
            $table->float('Quantity')->nullable();
            $table->string('UomCode')->nullable();
            $table->string('VatGroup')->nullable();
            $table->integer('is_selected')->nullable()->default(0);
            $table->float('QtySelected')->nullable()->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('trx_purchase_orders_items');
    }
}
