<div class="form-group row" style="padding-top:5px;">
    <label class="control-label col-xs-2" style="padding-top:5px; text-align:right">{{$label}}<span class="text-danger">*</span></label>
    <div class="col-sm-9">
        <select class="form-control select2" name="{{$name}}">
            <option value="">Select {{$label}}...</option>
            @foreach ($option as $o)
                <option value="{{$o->key}}" {{ $o->key == $value ? 'selected' : '' }}>{{$o->value}}</option>
            @endforeach
        </select>
    </div>
</div>