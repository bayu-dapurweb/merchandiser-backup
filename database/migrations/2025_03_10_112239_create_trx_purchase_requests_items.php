<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxPurchaseRequestsItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_purchase_requests_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trx_purchase_requests_id')->nullable();
            $table->integer('ref_item_master_datas_id')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->float('qty')->nullable();
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
        Schema::dropIfExists('trx_purchase_requests_items');
    }
}
