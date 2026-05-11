<tr class="item-row" data-row="{{ $index }}">
    <td>{{ $index }}.</td>
    <td>
        <select name="items[name][]" data-no="{{ $index }}" class="form-control select-item-master-data"
                {{ $disabled ? 'disabled' : '' }} {{ $isFirst ? 'required' : '' }}>
            <option value="">-Select Items-</option>
            @if (!empty($itemmaster))
                @foreach($itemmaster as $v)
                <option value="{{ $v->id }}"
                    {{ ($item && $item->ref_item_master_datas_id == $v->id ? 'selected' : '') }}>
                    {{ $v->name }} [{{ $v->sku }}] [{{ $v->unit_of_measurement }}]
                </option>
                @endforeach
            @endif
        </select>
    </td>
    <td>
        <input type="number" name="items[qty][]" class="form-control"
               value="{{ $item ? $item->qty : '' }}" min="1"
               {{ $disabled ? 'disabled' : '' }} {{ $isFirst ? 'required' : '' }}>
    </td>
    @if(!$disabled && $canEdit)
    <td>
        @if($isFirst)
            <button type="button" id="add-more" class="btn btn-xs btn-primary">
                <i class="fa fa-plus"></i> Add More
            </button>
        @else
            <button type="button" class="btn btn-xs btn-danger remove-item">
                <i class="fa fa-trash"></i> Remove
            </button>
        @endif
    </td>
    @endif
</tr>
