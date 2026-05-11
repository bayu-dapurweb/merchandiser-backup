<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefItemMasterDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_item_master_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->integer('item_group_code')->nullable();
            $table->integer('default_sales_uom')->nullable();
            $table->string('sales_unit')->nullable();
            $table->integer('default_purchasing_uom')->nullable();
            $table->string('purchasing_unit')->nullable();
            $table->string('inventory_item')->nullable();
            $table->string('purchase_item')->nullable();
            $table->string('sales_item')->nullable();
            $table->string('valid')->nullable();
            $table->string('frozen')->nullable();
            $table->string('sales_vat_group')->nullable();
            $table->string('purchase_vat_group')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_item_master_datas');
    }
}
