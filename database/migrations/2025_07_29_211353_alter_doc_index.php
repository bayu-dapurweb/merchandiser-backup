<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDocIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trx_goods_receipts', function (Blueprint $table) {
            $table->index('trx_purchase_orders_id');
            $table->index('U_SOL_SYNC_KEY');
            $table->index('CardCode');
            $table->index('WhsCode');
            $table->index('U_SOL_REF_KEY');
            $table->index('U_SOL_RAV_TRID');
            $table->index('doc_status');
            $table->index('sync_status');
            $table->index('is_verified');
            $table->index('verified_by_cms_users_id');
            $table->index('created_by');
            $table->index('approved_by');
        });

        Schema::table('trx_goods_receipt_items', function (Blueprint $table) {
            $table->index('trx_goods_receipts_id');
            $table->index('ItemCode');
            $table->index('UomCode');
        });

        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->index('trx_goods_receipts_id');
            $table->index('trx_purchase_orders_id');
            $table->index('U_SOL_SYNC_KEY');
            $table->index('CardCode');
            $table->index('WhsCode');
            $table->index('U_SOL_REF_KEY');
            $table->index('U_SOL_RAV_TRID');
            $table->index('doc_status');
            $table->index('sync_status');
            $table->index('is_verified');
            $table->index('verified_by_cms_users_id');
            $table->index('created_by');
            $table->index('approved_by');
            $table->index('NumAtCard');
        });

        Schema::table('trx_goods_return_items', function (Blueprint $table) {
            $table->index('trx_goods_returns_id');
            $table->index('ItemCode');
            $table->index('UomCode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trx_goods_receipts', function (Blueprint $table) {
            $table->dropIndex(strtolower('trx_goods_receipts_trx_purchase_orders_id_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_U_SOL_SYNC_KEY_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_CardCode_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_WhsCode_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_U_SOL_REF_KEY_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_U_SOL_RAV_TRID_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_doc_status_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_sync_status_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_is_verified_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_verified_by_cms_users_id_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_created_by_index'));
            $table->dropIndex(strtolower('trx_goods_receipts_approved_by_index'));
        });

        Schema::table('trx_goods_receipt_items', function (Blueprint $table) {
            $table->dropIndex(strtolower('trx_goods_receipt_items_trx_goods_receipts_id_index'));
            $table->dropIndex(strtolower('trx_goods_receipt_items_ItemCode_index'));
            $table->dropIndex(strtolower('trx_goods_receipt_items_UomCode_index'));
        });

        Schema::table('trx_goods_returns', function (Blueprint $table) {
            $table->dropIndex(strtolower('trx_goods_returns_trx_goods_receipts_id_index'));
            $table->dropIndex(strtolower('trx_goods_returns_trx_purchase_orders_id_index'));
            $table->dropIndex(strtolower('trx_goods_returns_U_SOL_SYNC_KEY_index'));
            $table->dropIndex(strtolower('trx_goods_returns_CardCode_index'));
            $table->dropIndex(strtolower('trx_goods_returns_WhsCode_index'));
            $table->dropIndex(strtolower('trx_goods_returns_U_SOL_REF_KEY_index'));
            $table->dropIndex(strtolower('trx_goods_returns_U_SOL_RAV_TRID_index'));
            $table->dropIndex(strtolower('trx_goods_returns_doc_status_index'));
            $table->dropIndex(strtolower('trx_goods_returns_sync_status_index'));
            $table->dropIndex(strtolower('trx_goods_returns_is_verified_index'));
            $table->dropIndex(strtolower('trx_goods_returns_verified_by_cms_users_id_index'));
            $table->dropIndex(strtolower('trx_goods_returns_created_by_index'));
            $table->dropIndex(strtolower('trx_goods_returns_approved_by_index'));
            $table->dropIndex(strtolower('trx_goods_returns_NumAtCard_index'));
        });

        Schema::table('trx_goods_return_items', function (Blueprint $table) {
            $table->dropIndex(strtolower('trx_goods_return_items_trx_goods_returns_id_index'));
            $table->dropIndex(strtolower('trx_goods_return_items_ItemCode_index'));
            $table->dropIndex(strtolower('trx_goods_return_items_UomCode_index'));
        });
    }
}
