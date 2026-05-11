<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrxGoodsReceiptsIdToTrxGoodsReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('trx_goods_receipts_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->dropColumn('trx_goods_receipts_id');
        });
    }
}
