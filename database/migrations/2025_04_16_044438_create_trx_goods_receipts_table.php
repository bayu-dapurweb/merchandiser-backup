<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxGoodsReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_goods_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trx_purchase_orders_id')->nullable();
            $table->string('U_SOL_SYNC_KEY')->nullable();
            $table->string('CardCode')->nullable();
            $table->string('DocDate')->nullable();
            $table->string('NumAtCard')->nullable();
            $table->string('WhsCode')->nullable();
            $table->string('Comments')->nullable();
            $table->string('U_SOL_REF_KEY')->nullable();
            $table->string('U_SOL_RAV_TRID')->nullable();
            $table->string('doc_status')->nullable();
            $table->string('sync_status')->nullable();
            $table->datetime('sync_at')->nullable();
            $table->integer('is_verified')->nullable()->default(0);
            $table->integer('verified_by_cms_users_id')->nullable();
            $table->datetime('verified_at')->nullable();
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
        Schema::dropIfExists('trx_goods_receipts');
    }
}
