<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByAndApprovedByToTrxPurchaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->integer('created_by')->unsigned()->nullable()->after('trx_purchase_orders_id');
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
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'approved_by']);
        });
    }
}
