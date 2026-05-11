@php 
$segment_action = request()->segment(3);
$segment_id = request()->segment(4);
$items = [];
$GRPOedItemss = [];
if ($segment_action == 'edit') {
    $items = \App\TrxGoodsReceiptItems::where('trx_goods_receipts_id',$segment_id)->get()->map(function($item) {
        $poItem = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $item->trx_goods_receipts->trx_purchase_orders_id ?? null)
            ->where('ItemCode', $item->ItemCode)
            ->first();
        if (!$item->UomCode || $item->UomCode == '') {
            if ($poItem && $poItem->UomCode) {
                $item->UomCode = $poItem->UomCode;
            } else {
                $prod = \App\RefItemMasterData::where('sku', $item->ItemCode)->first();
                if ($prod) {
                    $uom = \App\RefUoms::where('code', strtoupper($prod->unit_of_measurement))->first();
                    $item->UomCode = $uom ? $uom->code : $prod->unit_of_measurement;
                }
            }
        }
        return $item;
    });

    $theGRPO = \App\TrxGoodsReceipts::find($segment_id);
    $thePO = \App\TrxPurchaseOrders::find($theGRPO->trx_purchase_orders_id);
    if ($theGRPO->doc_status == 'approved') {
        $relatedGRPOs = \App\TrxGoodsReceipts::where('trx_purchase_orders_id', $thePO->id)
            ->where('doc_status', 'approved')
            ->get();
    } else {
        $relatedGRPOs = \App\TrxGoodsReceipts::where('trx_purchase_orders_id', $thePO->id)
            ->where('id', '!=', $theGRPO->id)
            ->where('doc_status', 'approved')
            ->get();
    }

    $GRPOedItems = [];

    foreach ($relatedGRPOs as $v) {
        $GRPOedItems = array_merge($GRPOedItems, \App\TrxGoodsReceiptItems::where('trx_goods_receipts_id', $v->id)->get()->toArray());
    }

    $POItems = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $thePO->id)->get();

    
    
    $POItemsMap = $POItems->pluck('Quantity', 'ItemCode')->toArray();
    
    // dd($POItemsMap);


    $GRPOedItemss = empty($GRPOedItems) ? [] : collect($GRPOedItems)
        ->groupBy('ItemCode')
        ->map(function($items, $itemCode) use ($POItemsMap) {
            return [
                'total_qty' => collect($items)->sum('Quantity'),
                'po_qty' => $POItemsMap[$itemCode] ?? 0
            ];
        })
        ->toArray();

    // dd($GRPOedItemss);
}
@endphp

<div class="col-sm-12">
    <table class="table table-borded">
        <thead>
            <tr>
                <td>No.</td>
                <td>Item</td>
                <td>Qty</td>
                <td>UomCode</td>
                @if($theGRPO->doc_status != 'approved')
                <td>Already Received</td>
                @endif
            </tr>
        </thead>
        <tbody id="item-table">
            @php $no = 1; @endphp
            @foreach ($items as $k => $item)
            <tr class="no-{{$no}}">
                <td>{{$no}}.</td>
                <td>
                    <input type="hidden" class="form-control" name="items[name][]" value="{{$item->ItemCode}}" readonly>
                    {{ $map_itemmaster[$item->ItemCode]->name ?? $item->ItemCode }} [{{ $item->ItemCode }}]
                </td>
                <td>
                    @php
                        $total_qty = $GRPOedItemss[$item->ItemCode]['total_qty'] ?? 0;
                        $po_qty = $POItemsMap[$item->ItemCode] ?? 0;
                        
                        if ($theGRPO->doc_status == 'approved') {
                            // For approved status, show actual quantity without adjustment
                            $input_qty = $item->Quantity;
                            $max_qty = $item->Quantity;
                        } else {
                            // For non-approved status, use remaining quantity logic
                            $remaining_qty = $total_qty >= $po_qty ? 0 : ($total_qty > 0 ? $po_qty - $total_qty : $item->Quantity);
                            $input_qty = $item->Quantity > $remaining_qty ? $remaining_qty : $item->Quantity;
                            $max_qty = $remaining_qty;
                        }
                    @endphp
                    <input type="number" name="items[Quantity][]" class="form-control grpoqty" style="width: 100px" 
                        max="{{ $max_qty }}" 
                        min="0"
                        value="{{ $input_qty }}"  
                        {{ ($disabled ?? false) || ($theGRPO->doc_status != 'approved' && $total_qty >= $po_qty) ? 'readonly' : '' }}>
                </td>
                <td>
                    <input type="text" name="items[UomCode][]" class="form-control" value="{{ $item->UomCode ?? '' }}" readonly>
                </td>
                @if($theGRPO->doc_status != 'approved')
                <td>
                    {{ $total_qty }}/{{ $po_qty }}
                </td>
                @endif
            </tr>
            @php $no++ @endphp
            @endforeach
        </tbody>
    </table>
</div>

<script>
<?php if(isset($disabled) && $disabled) { ?>
    $(document).ready(function() {
        $('#form > div.box-footer > div > div > input').remove();
    });   
<?php } ?>

$('#form').on('submit', (e) => {
    const hasZeroQty = $('.grpoqty').get().every(el => $(el).val() == 0);
    if (hasZeroQty) {
        e.preventDefault();
        alert("Cannot set all quantity to 0");
    } else {
        // Prevent double submission
        $('#form :submit').prop('disabled', true);
    }
});
</script>