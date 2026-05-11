<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapItemPrevixRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_item_previx_regions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("ref_branches_id")->nullable();
            $table->string("item_previx_code")->nullable();
            $table->timestamps();
        });
    }

    /**git statu
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map_item_previx_regions');
    }
}
