<div class="form-group row">
    <label class='control-label col-sm-2'>{{$label}}</label>
    <div class="col-sm-10">
        <select class="form-control select2" name="{{$name}}">
            <option value="">Select {{$label}}...</option>
            @foreach ($option as $o)
                <option value="{{$o->key}}" {{ $o->key == $selected ? 'selected' : '' }}>{{$o->value}}</option>
            @endforeach
        </select>
    </div>
</div>