<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterItemmasterAddVat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ref_item_master_datas', function (Blueprint $table) {
            $table->string('vatcode')->nullable()->default('VATI11');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ref_item_master_datas', function (Blueprint $table) {
            $table->dropColumn('vatcode');
        });
    }
}
