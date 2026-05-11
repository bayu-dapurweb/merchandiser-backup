<div class="form-group row" style="padding-top:5px;">
    <label class="control-label col-xs-2" style="padding-top:5px; text-align:right">{{$label}}
        @if ($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    <div class="col-sm-9">
        <input class="form-control w-100" placeholder="{{$placeholder}}" name="{{$name}}" value="{{$value}}">
        @if (!empty($errors))
            @foreach ($errors as $e)
            <small class="text-danger"><i>{{$e}}</i></small>
            @endforeach
        @endif
    </div>
</div>