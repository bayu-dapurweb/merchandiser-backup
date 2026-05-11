<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchaseRequestSync extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->string('sync_status')->nullable();
            $table->datetime('sync_at')->nullable();
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
            $table->dropColumn('sync_status');
            $table->dropColumn('sync_at');
        });
    }
}
