<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterItemAddPrefixes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ref_item_master_datas', function (Blueprint $table) {
            $table->string('previx')->nullable();
        });

        $items = \App\RefItemMasterData::get();
        foreach($items as $v) {
            $v->previx = substr($v->sku,0,3);
            $v->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ref_item_master_datas', function (Blueprint $table) {
            $table->dropColumn('item_prefix');
        });
    }
}
