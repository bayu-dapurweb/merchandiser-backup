<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxGoodsReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_goods_return_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trx_goods_returns_id')->nullable();
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
        Schema::dropIfExists('trx_goods_return_items');
    }
}
