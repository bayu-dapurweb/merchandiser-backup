<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPrAddVerified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_purchase_requests', function (Blueprint $table) {
            $table->integer('is_verified')->nullable()->default(0);
            $table->integer('verified_by_cms_users_id')->nullable();
            $table->datetime('verified_at')->nullable();
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
            $table->dropColumn('is_verified');
            $table->dropColumn('verified_by_cms_users_id');
            $table->dropColumn('verified_at');
        });
    }
}
