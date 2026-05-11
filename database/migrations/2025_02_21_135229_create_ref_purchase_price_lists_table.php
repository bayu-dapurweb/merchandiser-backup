<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefPurchasePriceListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_purchase_price_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_item_master_datas_id')->nullable();
            $table->string('item_code')->nullable();
            $table->float('price')->nullable();
            $table->string('card_code')->nullable();
            $table->string('ref_business_partners_id')->nullable();
            $table->string('var_group')->nullable();
            $table->integer('tax_rate')->nullable();
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
        Schema::dropIfExists('ref_purchase_price_lists');
    }
}
