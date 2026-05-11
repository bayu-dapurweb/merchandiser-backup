<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseRequestAddCallTry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->integer('api_try')->nullable()->default(0);
        });
        Schema::table('trx_purchase_orders', function (Blueprint $table) {
            $table->integer('api_try')->nullable()->default(0);
        });
        Schema::table('trx_goods_receipts', function (Blueprint $table) {
            $table->integer('api_try')->nullable()->default(0);
        });
        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->integer('api_try')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->dropColumn('api_try');
        });
        Schema::table('trx_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('api_try');
        });
        Schema::table('trx_goods_receipts', function (Blueprint $table) {
            $table->dropColumn('api_try');
        });
        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->dropColumn('api_try');
        });
    }
}
