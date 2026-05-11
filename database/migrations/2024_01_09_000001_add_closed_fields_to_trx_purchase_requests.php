<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClosedFieldsToTrxPurchaseRequests extends Migration
{
    public function up()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['is_closed', 'closed_at']);
        });
    }
}
