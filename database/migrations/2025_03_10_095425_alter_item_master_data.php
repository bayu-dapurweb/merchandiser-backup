<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterItemMasterData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('ref_item_master_datas', function (Blueprint $table) {
            $table->string('product_header_id')->nullable();
            $table->string('product_type')->nullable();
            $table->string('vendor')->nullable();
            $table->string('attribute')->nullable();
            $table->string('tags')->nullable();
            $table->string('product_id')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('barcode')->nullable();
            $table->string('sku')->nullable();
            $table->float('price')->nullable();
            $table->float('outlet_prices')->nullable();
            $table->boolean('taxable')->nullable();
            $table->boolean('track_inventory')->nullable();
            $table->boolean('allow_negative_stock')->nullable();
            $table->float('sold_count')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->float('buying_prices')->nullable();
            $table->float('buying_price')->nullable();
            $table->longtext('inventories')->nullable();
            $table->longtext('ingredients')->nullable();
            $table->longtext('variant_options')->nullable();
            $table->longtext('bundles')->nullable();
            $table->boolean('is_active')->nullable();
            $table->datetime('modified_date')->nullable();
        });

        Schema::table('ref_item_master_datas', function (Blueprint $table) {
            $table->dropColumn('item_code');
            $table->dropColumn('item_name');
            $table->dropColumn('item_group_code');
            $table->dropColumn('default_sales_uom');
            $table->dropColumn('sales_unit');
            $table->dropColumn('default_purchasing_uom');
            $table->dropColumn('purchasing_unit');
            $table->dropColumn('inventory_item');
            $table->dropColumn('purchase_item');
            $table->dropColumn('sales_item');
            $table->dropColumn('valid');
            $table->dropColumn('frozen');
            $table->dropColumn('sales_vat_group');
            $table->dropColumn('purchase_vat_group');
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
            $table->dropColumn('product_header_id');
            $table->dropColumn('product_type');
            $table->dropColumn('vendor');
            $table->dropColumn('attribute');
            $table->dropColumn('tags');
            $table->dropColumn('product_id');
            $table->dropColumn('name');
            $table->dropColumn('type');
            $table->dropColumn('barcode');
            $table->dropColumn('sku');
            $table->dropColumn('price');
            $table->dropColumn('outlet_prices');
            $table->dropColumn('taxable');
            $table->dropColumn('track_inventory');
            $table->dropColumn('allow_negative_stock');
            $table->dropColumn('sold_count');
            $table->dropColumn('unit_of_measurement');
            $table->dropColumn('buying_prices');
            $table->dropColumn('buying_price');
            $table->dropColumn('inventories');
            $table->dropColumn('ingredients');
            $table->dropColumn('variant_options');
            $table->dropColumn('bundles');
            $table->dropColumn('is_active');
            $table->dropColumn('modified_date');
        });

        Schema::table('ref_item_master_datas', function (Blueprint $table) {
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
        });
    }
}
