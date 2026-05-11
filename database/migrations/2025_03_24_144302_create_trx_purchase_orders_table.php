<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrxPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trx_purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trx_purchase_requests_id')->nullable();
            $table->string('doc_status')->nullable();
            $table->string('U_SOL_SYNC_KEY')->nullable();
            $table->string('CardCode')->nullable();
            $table->date('DocDate')->nullable();
            $table->string('WhsCode')->nullable();
            $table->text('Comments')->nullable();
            $table->float('DiscPrcnt')->nullable();
            $table->float('DiscSum')->nullable();
            $table->float('DocTotal')->nullable();
            $table->string('U_SOL_REF_KEY')->nullable();
            $table->string('U_SOL_RAV_TRID')->nullable();
            $table->integer('is_verified')->nullable()->default(0);
            $table->integer('verified_by_cms_users_id')->nullable();
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
        Schema::dropIfExists('trx_purchase_orders');
    }
}
