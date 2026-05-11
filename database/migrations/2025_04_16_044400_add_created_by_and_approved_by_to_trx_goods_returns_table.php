<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByAndApprovedByToTrxGoodsReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->integer('created_by')->unsigned()->nullable()->after('verified_at');
            $table->integer('approved_by')->unsigned()->nullable()->after('created_by');
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
            $table->dropColumn(['created_by', 'approved_by']);
        });
    }
}
