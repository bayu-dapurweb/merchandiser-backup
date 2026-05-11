<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBranchesTable extends Migration
{
    public function up()
    {
        Schema::create('product_branches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ref_branches_id');
            $table->integer('ref_item_master_datas_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_branches');
    }
}
