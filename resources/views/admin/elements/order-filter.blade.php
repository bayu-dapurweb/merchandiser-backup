<div class="panel panel-default">
    <div class="panel-heading">
        Advance Filter
    </div>

    <div class="panel-body">
        <form action="" class="form-inline">
            @foreach ($param as $v)
                @if ($v["type"] == "input" )
                <input type="text" class="form-control" name="{{$v['name']}}" placeholder="{{$v['label']}}" value="{{$v['value']}}">
                @endif
                @if ($v["type"] == "date" )
                <input type="date" class="form-control" name="{{$v['name']}}" placeholder="{{$v['label']}}" value="{{$v['value']}}">
                @endif
                @if ($v["type"] == "select" )
                <select class="form-control" name="{{$v['name']}}">
                    <option value="">Select {{$v['label']}}</option>
                    @if(!empty($v['options']))
                        @foreach ($v['options'] as $o)
                        <option value="{{$o['value']}}" {{ $v['value'] == $o['value'] ? "selected" : "" }}>{{$o['label']}}</option>
                        @endforeach
                    @endif
                </select>
                @endif
            @endforeach
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>Search</button>
            <button type="submit" class="btn btn-success" name="export" value='1'><i class="fa fa-file"></i>Export</button>
        </form>
        <style>
            #btn_advanced_filter {
                display: none;
            }
            .box-tools form {
                display: none !important;
            }
        </style>
    </div>
</div>