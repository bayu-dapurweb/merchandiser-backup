<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxPurchaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_purchase_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('U_SOL_SYNC_KEY')->nullable();
            $table->string('ReqName')->nullable();
            $table->date('DocDate')->nullable();
            $table->string('Branch')->nullable();
            $table->string('U_VIT_ToStr')->nullable();
            $table->text('Comments')->nullable();
            $table->string('U_SOL_RAV_TRID')->nullable();
            $table->integer('is_have_purchase_order')->nullable()->default(0);
            $table->integer('trx_purchase_orders_id')->nullable();
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
        Schema::dropIfExists('trx_purchase_requests');
    }
}
