@php 
$segment_action = request()->segment(3);
$segment_id = request()->segment(4);
$items = [];
$ReceivedItemss = [];
if ($segment_action == 'edit') {
    $items = \App\TrxGoodsReturnItems::where('trx_goods_returns_id',$segment_id)->get()->map(function($r){
        $prod = \App\RefItemMasterData::where("sku" , $r->ItemCode)->first();
        $uom = \App\RefUoms::where("code", strtoupper($prod->unit_of_measurement))->first();
        $r->UomCode = $uom->code;
        return $r;
    });

    $theGReturn = \App\TrxGoodsReturns::find($segment_id);
    $theGRPO = \App\TrxGoodsReceipts::find($theGReturn->trx_goods_receipts_id ?? null);
    
    if ($theGRPO) {
        // Get other approved returns from the same GRPO
        if ($theGReturn->doc_status == 'approved') {
            $relatedReturns = \App\TrxGoodsReturns::where('trx_goods_receipts_id', $theGRPO->id)
                ->where('doc_status', 'approved')
                ->get();
        } else {
            $relatedReturns = \App\TrxGoodsReturns::where('trx_goods_receipts_id', $theGRPO->id)
                ->where('id', '!=', $theGReturn->id)
                ->where('doc_status', 'approved')
                ->get();
        }

        $ReturnedItems = [];
        foreach ($relatedReturns as $v) {
            $ReturnedItems = array_merge($ReturnedItems, \App\TrxGoodsReturnItems::where('trx_goods_returns_id', $v->id)->get()->toArray());
        }

        // Get the received items from the GRPO
        $ReceivedItems = \App\TrxGoodsReceiptItems::where('trx_goods_receipts_id', $theGRPO->id)->get();
        $ReceivedItemsMap = $ReceivedItems->pluck('Quantity', 'ItemCode')->toArray();

        $ReceivedItemss = $ReceivedItems->groupBy('ItemCode')
            ->map(function($items, $itemCode) {
                return [
                    'total_qty' => collect($items)->sum('Quantity'),
                    'received_qty' => collect($items)->sum('Quantity')
                ];
            })
            ->toArray();

        // Calculate already returned quantities
        $ReturnedItemss = empty($ReturnedItems) ? [] : collect($ReturnedItems)
            ->groupBy('ItemCode')
            ->map(function($items) {
                return collect($items)->sum('Quantity');
            })
            ->toArray();
    }
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
                @if($theGReturn->doc_status != 'approved')
                <td>Already Returned</td>
                @endif
            </tr>
        </thead>
        <tbody id="item-table">
            
            @php $no = 1; @endphp
            <tr>
                <td>{{$no}}.</td>
                <td>
                    <input type="hidden" class="form-control" name="items[name][]" value="{{$items[0]->ItemCode}}" readonly>
                     {{ $map_itemmaster[$items[0]->ItemCode]->name }} [{{ $items[0]->ItemCode }}]
                </td>
                
                <td>
                    @php
                        $received_qty = $ReceivedItemss[$items[0]->ItemCode]['received_qty'] ?? $items[0]->Quantity;
                        $already_returned = $ReturnedItemss[$items[0]->ItemCode] ?? 0;
                        
                        if ($theGReturn->doc_status == 'approved') {
                            // For approved status, show actual quantity without adjustment
                            $return_qty = $items[0]->Quantity;
                            $max_qty = $items[0]->Quantity;
                        } else {
                            // For non-approved status, use remaining quantity logic
                            $remaining_qty = $received_qty - $already_returned;
                            $return_qty = $items[0]->Quantity > $remaining_qty ? ($remaining_qty > 0 ? $remaining_qty : 0) : $items[0]->Quantity;
                            $max_qty = $remaining_qty;
                        }
                    @endphp
                    <input type="number" name="items[Quantity][]" class="form-control returnqty" style="width: 100px"
                        max="{{ $max_qty }}" 
                        min="0"
                        value="{{ $return_qty }}" 
                        {{ ($disabled ?? false) || ($theGReturn->doc_status != 'approved' && $remaining_qty <= 0) ? 'readonly' : '' }}>
                </td>
                <td>
                    <input type="text" name="items[UomCode][]" class="form-control" value="{{( !empty($items[0]) && $items[0]->UomCode ? $items[0]->UomCode : '' ) }}" readonly>
                </td>
                @if($theGReturn->doc_status != 'approved')
                <td>
                    {{ $already_returned }}/{{ $received_qty }}
                </td>
                @endif
            </tr>
            @foreach ($items as $k => $item) 
            @php if ($k == 0) { continue; } @endphp
            @php $no++ @endphp
            <tr class="no-{{$no}}">
                <td>{{$no}}.</td>
                <td>
                    
                    {{ $map_itemmaster[$item->ItemCode]->name }} [{{ $item->ItemCode }}]
                    <input type="hidden" class="form-control" name="items[name][]" value="{{$item->ItemCode}}" readonly>
                </td>
                
                <td>
                    @php
                        $received_qty = $ReceivedItemss[$item->ItemCode]['received_qty'] ?? $item->Quantity;
                        $already_returned = $ReturnedItemss[$item->ItemCode] ?? 0;
                        
                        if ($theGReturn->doc_status == 'approved') {
                            // For approved status, show actual quantity without adjustment
                            $return_qty = $item->Quantity;
                            $max_qty = $item->Quantity;
                        } else {
                            // For non-approved status, use remaining quantity logic
                            $remaining_qty = $received_qty - $already_returned;
                            $return_qty = $item->Quantity > $remaining_qty ? ($remaining_qty > 0 ? $remaining_qty : 0) : $item->Quantity;
                            $max_qty = $remaining_qty;
                        }
                    @endphp
                    <input type="number" name="items[Quantity][]" class="form-control returnqty" style="width: 100px"
                        max="{{ $max_qty }}" 
                        min="0"
                        value="{{ $return_qty }}" 
                        {{ ($disabled ?? false) || ($theGReturn->doc_status != 'approved' && $remaining_qty <= 0) ? 'readonly' : '' }}>
                </td>
                <td>
                    <input type="text" name="items[UomCode][]" class="form-control" value="{{ $item->UomCode }}" readonly>
                </td>
                @if($theGReturn->doc_status != 'approved')
                <td>
                    {{ $already_returned }}/{{ $received_qty }}
                </td>
                @endif
            </tr>
            
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

    
var itemmaster = '{!! json_encode($itemmaster) !!}';
var item_master_obj = JSON.parse(itemmaster);

var tax_master = '{!! json_encode($tax_groups) !!}';
var vat_master_obj = JSON.parse(tax_master)

var no = {{ $no }};

$('#add-more').click(function(){
    no++;
    option_html = '';
    $.each(item_master_obj, (k,v) => {
        option_html = option_html + `<option value="`+v.sku+`">`+ v.name +` [`+v.sku+`] [`+v.unit_of_measurement+`]</option>`;
    })

    option_vat_html = '';
    $.each(vat_master_obj, (k,v) => {
        option_vat_html = option_vat_html + `<option value="`+v.code+`">`+ v.name +`</option>`;
    })
    
    var html = `
    <tr class="no-`+no+`">
        <td>`+no+`.</td>
        <td>
            <select name="items[name][]" id="" class="form-control">
                <option value="">-Select Items-</option>
                `+ option_html +`
            </select>
        </td>

        <td>
            <input type="number" name="items[Quantity][]" class="form-control" value="">
        </td>
        <td>
            <input type="number" name="items[PriceBefDi][]" class="form-control" value="">
        </td>
        <td>
            <select name="items[VatGroup][]" id="" class="form-control">
                <option value="">-Select VAT-</option>
                `+ option_vat_html +`
            </select>
        </td>
        
        <td>
            <a class="btn btn-xs btn-danger" onclick="$('.no-`+no+`').remove()"><i class="fa fa-trash"></i> Remove</a>
        </td>
    </tr>`;    

    $("#item-table").append(html);
})

$('#form').on('submit', (e) => {
    const hasZeroQty = $('.returnqty').get().every(el => $(el).val() == 0);
    if (hasZeroQty) {
        e.preventDefault();
        alert("Cannot set all quantity to 0");
    } else {
        // Prevent double submission
        $('#form :submit').prop('disabled', true);
    }
});

</script>