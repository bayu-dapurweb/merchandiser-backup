@php 
$segment_action = request()->segment(3);
$segment_id = request()->segment(4);
$items = [];
$POedItemss = [];
if ($segment_action == 'edit') {
    $items = \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id',$segment_id)->get();
    // dd($items);

    $thePO = \App\TrxPurchaseOrders::find($segment_id);
    
    if ($thePO->is_verified == true) {
        $relatedPOs = \App\TrxPurchaseOrders::where('trx_purchase_requests_id', $thePO->trx_purchase_requests_id)
            ->where('is_verified', true)
            ->whereNotNull('parent_id')
            ->get();
    } else {
        $relatedPOs = \App\TrxPurchaseOrders::where('trx_purchase_requests_id', $thePO->trx_purchase_requests_id)
            ->where('id', '!=', $thePO->id)
            ->where('is_verified', true)
            ->whereNotNull('parent_id')
            ->get();
    }

    // var_dump($relatedPOs);
    $POedItems = [];
    foreach ($relatedPOs as $v) {
        $POedItems = array_merge($POedItems, \App\TrxPurchaseOrdersItems::where('trx_purchase_orders_id', $v->id)->get()->toArray());
    }

    $PRItems = \App\TrxPurchaseRequestsItems::select('trx_purchase_requests_items.*', 'ref_item_master_datas.sku as ItemCode')
        ->join('ref_item_master_datas', 'ref_item_master_datas.id', '=', 'trx_purchase_requests_items.ref_item_master_datas_id')
        ->where('trx_purchase_requests_id', $thePO->trx_purchase_requests_id)
        ->get();
    
    $PRItemsMap = $PRItems->pluck('qty', 'ItemCode')->toArray();
    
    $POedItemss = empty($POedItems) ? [] : collect($POedItems)
        ->groupBy('ItemCode')
        ->map(function($items, $itemCode) use ($PRItemsMap) {
            return [
                'total_qty' => $items->sum('Quantity'),
                'pr_qty' => $PRItemsMap[$itemCode] ?? 0
            ];
        })
        ->toArray();

    // var_dump($PRItemsMap);
}

@endphp

<div class="col-sm-10">
    <table class="table table-borded">
        <thead>
            <tr>
                <td>No.</td>
                <td>Item</td>
                <td>Vendor</td>
                <td>Qty</td>
                <td>Price</td>
                <td>VatGroup</td>
                @if(!$hidepoed) <td>Already In PO</td> @endif
                
                {{-- <td>Action</td> --}}
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
                    <select name="{{ ($disabled || ($POedItemss[$items[0]->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$items[0]->ItemCode] ?? 0)) ? '' : 'items[ref_business_partners_id][]' }}" data-no="{{$no}}" data-uom="" 
                        class="form-control select2-business-partners" 
                        {{ ($disabled || ($POedItemss[$items[0]->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$items[0]->ItemCode] ?? 0)) ? "disabled" : '' }}>
                        <option value="">-Select Business Partners -</option>
                        @foreach($bpmaster as $v)
                        <option value="{{$v->id}}" data-vatcode="{{$v->vatcode}}" {{ ((!empty($items[0]) && $items[0]->ref_business_partners_id == $v->id ? 'selected' : ''))  }}>{{ $v->name }} [{{$v->code}}]</option>
                        @endforeach
                    </select>
                    @if($disabled || ($POedItemss[$items[0]->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$items[0]->ItemCode] ?? 0))
                        <input type="hidden" name="items[ref_business_partners_id][]" value="{{ $items[0]->ref_business_partners_id }}">
                    @endif
                </td>
                <td>
                    @php
                        $total_qty = $POedItemss[$items[0]->ItemCode]['total_qty'] ?? 0;
                        $pr_qty = $PRItemsMap[$items[0]->ItemCode] ?? 0;
                        
                        if ($thePO->is_verified == true) {
                            // For approved status, show actual quantity without adjustment
                            $input_qty = $items[0]->Quantity;
                            $max_qty = $items[0]->Quantity;
                        } else {
                            // For non-approved status, use remaining quantity logic
                            $remaining_qty = $total_qty >= $pr_qty ? 0 : ($total_qty > 0 ? $pr_qty - $total_qty : $items[0]->Quantity);
                            $input_qty = $items[0]->Quantity > $remaining_qty ? $remaining_qty : $items[0]->Quantity;
                            $max_qty = $remaining_qty;
                        }
                    @endphp
                    <input type="number" name="items[Quantity][]" class="form-control poqty " style="width: 100px" 
                        max="{{ $max_qty }}" 
                        min="0"
                        value="{{ $input_qty }}"  
                        {{ ($disabled || ($thePO->is_verified != true && $total_qty >= $pr_qty)) ? "readonly" : '' }}>
                </td>
                <td>
                    <input type="number" name="items[PriceBefDi][]" class="form-control" step="0.01"
                        value="{{( !empty($items[0]) && $items[0]->PriceBefDi > 0 ? $items[0]->PriceBefDi : '' ) }}"  
                        {{ ($disabled || ($POedItemss[$items[0]->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$items[0]->ItemCode] ?? 0)) ? "readonly" : '' }}>
                </td>
                
                <td class="vat-group-cell">
                    @php
                        $bp_id = $items[0]->ref_business_partners_id;
                        $vatcode = !empty($bp_id) && isset($map_bpmaster[$bp_id]) ? $map_bpmaster[$bp_id]->vatcode : null;
                    @endphp
                    @if($vatcode)
                        <input type="text" class="form-control vat-group-input" name="items[VatGroup][]" value="{{ $vatcode }}" readonly>
                    @else
                        <select name="items[VatGroup][]" class="form-control vat-group-input select2-vat">
                            <option value="">- Select VAT -</option>
                            @foreach($tax_groups as $tax)
                                <option value="{{ $tax->code }}" {{ $items[0]->VatGroup == $tax->code ? 'selected' : '' }}>{{ $tax->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </td>
                @if(!$hidepoed)
                <td>
                    {{ $POedItemss[$items[0]->ItemCode]['total_qty'] ?? 0 }}/{{ $PRItemsMap[$items[0]->ItemCode] ?? 0 }}
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
                    <select name="{{ ($disabled || ($POedItemss[$item->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$item->ItemCode] ?? 0)) ? '' : 'items[ref_business_partners_id][]' }}" data-no="{{$no}}" data-uom="" 
                        class="form-control select2-business-partners"  
                        {{ ($disabled || ($POedItemss[$item->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$item->ItemCode] ?? 0)) ? "disabled" : '' }}>
                        <option value="">-Select Business Partners -</option>
                        @foreach($bpmaster as $v)
                        <option value="{{$v->id}}" data-vatcode="{{$v->vatcode}}" {{ ((!empty($item) && $item->ref_business_partners_id == $v->id ? 'selected' : ''))  }}>{{ $v->name }} [{{$v->code}}]</option>
                        @endforeach
                    </select>
                    @if($disabled || ($POedItemss[$item->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$item->ItemCode] ?? 0))
                        <input type="hidden" name="items[ref_business_partners_id][]" value="{{ $item->ref_business_partners_id }}">
                    @endif
                </td>
                
                <td>
                    @php
                        $total_qty = $POedItemss[$item->ItemCode]['total_qty'] ?? 0;
                        $pr_qty = $PRItemsMap[$item->ItemCode] ?? 0;
                        
                        if ($thePO->is_verified == true) {
                            // For approved status, show actual quantity without adjustment
                            $input_qty = $item->Quantity;
                            $max_qty = $item->Quantity;
                        } else {
                            // For non-approved status, use remaining quantity logic
                            $remaining_qty = $total_qty >= $pr_qty ? 0 : ($total_qty > 0 ? $pr_qty - $total_qty : $item->Quantity);
                            $input_qty = $item->Quantity > $remaining_qty ? $remaining_qty : $item->Quantity;
                            $max_qty = $remaining_qty;
                        }
                    @endphp
                    <input type="number" name="items[Quantity][]" class="form-control poqty " style="width: 100px"  
                        max="{{ $max_qty }}" 
                        min="0"
                        value="{{ $input_qty }}"  
                        {{ ($disabled || ($thePO->is_verified != true && $total_qty >= $pr_qty)) ? "readonly" : '' }}>
                </td>
                <td>
                    <input type="number" name="items[PriceBefDi][]" class="form-control" step="0.01"
                        value="{{ $item->PriceBefDi }}"  
                        {{ ($disabled || ($POedItemss[$item->ItemCode]['total_qty'] ?? 0) >= ($PRItemsMap[$item->ItemCode] ?? 0)) ? "readonly" : '' }}>
                </td>
                
                <td class="vat-group-cell">
                    @php
                        $bp_id = $item->ref_business_partners_id;
                        $vatcode = !empty($bp_id) && isset($map_bpmaster[$bp_id]) ? $map_bpmaster[$bp_id]->vatcode : null;
                    @endphp
                    @if($vatcode)
                        <input type="text" class="form-control vat-group-input" name="items[VatGroup][]" value="{{ $vatcode }}" readonly>
                    @else
                        <select name="items[VatGroup][]" class="form-control vat-group-input select2-vat">
                            <option value="">- Select VAT -</option>
                            @foreach($tax_groups as $tax)
                                <option value="{{ $tax->code }}" {{ $item->VatGroup == $tax->code ? 'selected' : '' }}>{{ $tax->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </td>
                @if(!$hidepoed)
                <td>
                    {{ $POedItemss[$item->ItemCode]['total_qty'] ?? 0 }}/{{ $PRItemsMap[$item->ItemCode] ?? 0 }}
                </td>
                @endif
            </tr>
            
            @endforeach
        </tbody>
    </table>
</div>

<!-- Select2 CSS -->
<link rel="stylesheet" href="{{ asset('vendor/crudbooster/assets/select2/dist/css/select2.min.css') }}">
<style>
.select2-container--default .select2-selection--single {
    height: 34px;
    border: 1px solid #d2d6de;
}
.select2-container .select2-selection--single {
    height: 34px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 32px;
}
</style>

<!-- Select2 JavaScript -->
<script src="{{ asset('vendor/crudbooster/assets/select2/dist/js/select2.full.min.js') }}"></script>
<script>
    console.log("test");
    $(document).ready(function() {
        // Data from controller
        var item_master_obj = {!! json_encode($itemmaster) !!};
        var vat_master_obj = {!! json_encode($tax_groups) !!};
        var bp_master_obj = {!! json_encode($bpmaster) !!};
        var no = {{ $no }};

        // Remove save button if form is disabled and disable form elements
        <?php if($disabled) { ?>
            $('#form > div.box-footer > div > div > input').remove();
            // Disable all form inputs
            $('input, select, textarea, button').prop('disabled', true);
            // Make sure Select2 dropdowns are also disabled
            $('.select2-vat').prop('disabled', true).trigger('change');
        <?php } ?>

        // Generic Select2 Initializer
        function initSelect2(element, placeholder) {
            console.log(element);
            $(element).select2({
                placeholder: placeholder,
                allowClear: true,
                width: '100%'
            });
        }

        // Initial load - Initialize all existing Select2 elements
        initSelect2('.select2-business-partners', '-Select Business Partners-');
        initSelect2('.select2-vat', '- Select VAT -');
        
        // Initialize existing elements on page load
        $('.select2-business-partners').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                initSelect2(this, '-Select Business Partners-');
            }
            // Ensure disabled state is maintained
            if ($(this).prop('disabled')) {
                $(this).next('.select2').find('.select2-selection').css('background-color', '#e9ecef');
            }
        });
        
        $('.select2-vat').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                initSelect2(this, '- Select VAT -');
            }
            // Ensure disabled state is maintained
            if ($(this).prop('disabled')) {
                $(this).next('.select2').find('.select2-selection').css('background-color', '#e9ecef');
            }
        });
        
        // Add event handler for existing business partner dropdowns
        $(document).on('change', '.select2-business-partners', function() {
            const selectedOption = $(this).find('option:selected');
            const vatCode = selectedOption.data('vatcode');
            const vatCell = $(this).closest('tr').find('.vat-group-cell');
            
            // Build VAT options
            let vat_option_html = '<option value="">- Select VAT -</option>';
            $.each(vat_master_obj, (k, v) => {
                vat_option_html += `<option value="${v.code}">${v.name}</option>`;
            });
            
            if (vatCode) {
                // Replace select with readonly input
                vatCell.html(`<input type="text" class="form-control vat-group-input" name="items[VatGroup][]" value="${vatCode}" readonly>`);
            } else {
                // Show VAT select dropdown
                vatCell.html(`<select name="items[VatGroup][]" class="form-control vat-group-input select2-vat">${vat_option_html}</select>`);
                initSelect2(vatCell.find('.select2-vat'), '- Select VAT -');
            }
        });

        // Form submission validation
        $('#form').on('submit', function(e) {
            const vendorItems = {};
            let hasDuplicateVendor = false;
            
            // Check for duplicate vendors for the same item
            $('select[name^="items[ref_business_partners_id]"]:enabled').each(function() {
                const vendorId = $(this).val();
                // Correctly find the item code from the item select dropdown
                const itemCode = $(this).closest('tr').find('select[name^="items[name]"]').val();
                
                if (vendorId && itemCode) {
                    if (!vendorItems[itemCode]) {
                        vendorItems[itemCode] = new Set();
                    }
                    if (vendorItems[itemCode].has(vendorId)) {
                        hasDuplicateVendor = true;
                        return false; // Exit loop
                    }
                    vendorItems[itemCode].add(vendorId);
                }
            });

            if (hasDuplicateVendor) {
                e.preventDefault();
                alert('Error: Anda telah memilih vendor yang sama untuk item yang sama beberapa kali. Pastikan setiap item yang sama memiliki vendor yang berbeda.');
                return false;
            }

            // Check for zero quantity
            const hasZeroQty = $('.poqty').get().every(el => $(el).val() == 0 || $(el).val() == '');
            if (hasZeroQty) {
                e.preventDefault();
                alert("Error: Anda tidak dapat menyimpan PO dengan kuantitas 0 untuk setiap item. Silakan isi kuantitas yang valid untuk setiap item.");
                return false;
            }

            // Check for empty vendor fields
            const hasEmptyVendor = $('.select2-business-partners').get().some(el => $(el).val() === '' || $(el).val() === null);
            if (hasEmptyVendor) {
                e.preventDefault();
                alert("Error: Anda tidak dapat menyimpan PO tanpa memilih vendor untuk setiap item. Silakan pilih vendor yang valid untuk setiap item.");
                return;
            }

            // Prevent double submission
            $('#form :submit').prop('disabled', true);
        });

        // Event handler for vendor change to dynamically update VAT field
        $('#item-table').on('change', '.select2-business-partners', function() {
            var selectedOption = $(this).find('option:selected');
            var vatCode = selectedOption.data('vatcode');
            var row = $(this).closest('tr');
            var vatCell = row.find('.vat-group-cell');

            // Clean up previous select2 instance if it exists
            if (vatCell.find('.select2-vat').data('select2')) {
                vatCell.find('.select2-vat').select2('destroy');
            }

            if (vatCode) {
                var inputHtml = `<input type="text" class="form-control vat-group-input" name="items[VatGroup][]" value="${vatCode}" readonly>`;
                vatCell.html(inputHtml);
            } else {
                var selectHtml = `<select name="items[VatGroup][]" class="form-control vat-group-input select2-vat">`;
                selectHtml += `<option value="">- Select VAT -</option>`;
                $.each(vat_master_obj, function(k, v) {
                    selectHtml += `<option value="${v.code}">${v.name}</option>`;
                });
                selectHtml += `</select>`;
                vatCell.html(selectHtml);
                // Initialize select2 on the newly created VAT dropdown
                initSelect2(vatCell.find('.select2-vat'), '- Select VAT -');
            }
        });

        // Event handler for adding a new item row
        $('#add-more').click(function() {
            no++;
            
            // Build item options
            let item_option_html = '<option value="">-Select Items-</option>';
            $.each(item_master_obj, (k, v) => {
                item_option_html += `<option value="${v.sku}">${v.name} [${v.sku}] [${v.unit_of_measurement}]</option>`;
            });

            // Build business partner options from JS object
            let bp_option_html = '<option value="">-Select Business Partners-</option>';
            $.each(bp_master_obj, (k, v) => {
                bp_option_html += `<option value="${v.id}" data-vatcode="${v.vatcode || ''}">${v.name} [${v.code}]</option>`;
            });
            
            // Build VAT options from JS object
            let vat_option_html = '<option value="">- Select VAT -</option>';
            $.each(vat_master_obj, (k, v) => {
                vat_option_html += `<option value="${v.code}">${v.name}</option>`;
            });

            var html = `
            <tr class="no-${no}">
                <td>${no}.</td>
                <td>
                    <select name="items[name][]" class="form-control">${item_option_html}</select>
                </td>
                <td>
                    <select name="items[ref_business_partners_id][]" class="form-control select2-business-partners">
                        ${bp_option_html}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[Quantity][]" class="form-control poqty" value="">
                </td>
                <td>
                    <input type="number" name="items[PriceBefDi][]" class="form-control" step="0.01" value="">
                </td>
                <td class="vat-group-cell">
                    <select name="items[VatGroup][]" class="form-control vat-group-input select2-vat">
                        ${vat_option_html}
                    </select>
                </td>
                <td>
                    <a class="btn btn-xs btn-danger" onclick="$('.no-${no}').remove()"><i class="fa fa-trash"></i> Remove</a>
                </td>
            </tr>`;

            var newRow = $(html);
            $("#item-table").append(newRow);
            
            // Initialize select2 on the new dropdowns
            initSelect2(newRow.find('.select2-business-partners'), '-Select Business Partners-');
            initSelect2(newRow.find('.select2-vat'), '- Select VAT -');
            
            // Add event handler for business partner change to update VAT
            newRow.find('.select2-business-partners').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const vatCode = selectedOption.data('vatcode');
                const vatCell = $(this).closest('tr').find('.vat-group-cell');
                
                if (vatCode) {
                    // Replace select with readonly input
                    vatCell.html(`<input type="text" class="form-control vat-group-input" name="items[VatGroup][]" value="${vatCode}" readonly>`);
                } else {
                    // Show VAT select dropdown
                    vatCell.html(`<select name="items[VatGroup][]" class="form-control vat-group-input select2-vat">${vat_option_html}</select>`);
                    initSelect2(vatCell.find('.select2-vat'), '- Select VAT -');
                }
            });
        });
    });
</script>