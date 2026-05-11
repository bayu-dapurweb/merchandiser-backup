<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxGoodsReceiptItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_goods_receipt_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trx_goods_receipts_id')->nullable();
            $table->string('ItemCode')->nullable();
            $table->float('Quantity')->nullable();
            $table->string('UomCode')->nullable();
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
        Schema::dropIfExists('trx_goods_receipt_items');
    }
}
