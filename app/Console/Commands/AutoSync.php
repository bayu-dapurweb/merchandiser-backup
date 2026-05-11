<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data automatically at scheduled intervals';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$this->syncPurchaseRequest();
        //$this->syncPurchaseOrder();
        //$this->syncGoodsReceipt();
        //$this->syncGoodsReturn();
        
    }

    public function syncPurchaseRequest()
    {
        $purchase_request_failed = \App\TrxPurchaseRequests::where([
            ["sync_status", "Failed"],
            ["api_try", "<", env("API_TRY_LIMIT", 3)]
        ])->get();

        $pr_controller = new \App\Http\Controllers\AdminTrxPurchaseRequestsController;
        foreach ($purchase_request_failed as $pr) {
            $pr_controller->prSync($pr->id);
        }
    }

    public function syncPurchaseOrder()
    {
        $purchase_order_failed = \App\TrxPurchaseOrders::where([
            ["sync_status", "Failed"],
            ["api_try", "<", env("API_TRY_LIMIT", 3)]
        ])->get();

        $po_controller = new \App\Http\Controllers\AdminTrxPurchaseOrdersApprovedController;
        // $po_controller = new \App\Http\Controllers\AdminTrxPurchaseOrdersController;
        foreach ($purchase_order_failed as $po) {
            $po_controller->poSync($po->id);
        }
    }

    public function syncGoodsReceipt()
    {
        $goods_receipt_failed = \App\TrxGoodsReceipts::where([
            ["sync_status", "Failed"],
            ["api_try", "<", env("API_TRY_LIMIT", 3)]
        ])->get();

        $gr_controller = new \App\Http\Controllers\AdminTrxGoodsReceiptsController;
        foreach ($goods_receipt_failed as $gr) {
            $gr_controller->grpoSync($gr->id);
        }
    }

    public function syncGoodsReturn()
    {
        $goods_return_failed = \App\TrxGoodsReturns::where([
            ["sync_status", "Failed"],
            ["api_try", "<", env("API_TRY_LIMIT", 3)]
        ])->get();

        $gr_controller = new \App\Http\Controllers\AdminTrxGoodsReturnsController;
        foreach ($goods_return_failed as $gr) {
            $gr_controller->grpoSync($gr->id);
        }
    }

    
}
