<div class="panel panel-default">
    <div class="panel-heading">
        Filter
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
                @if ($v["type"] == "select2" )
                <select class="form-control filterselect2" name="{{$v['name']}}">
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
            @if ($with_export)
            <button type="submit" class="btn btn-success" name="export" value='1'><i class="fa fa-file"></i>Export</button>
            @endif
        </form>
    </div>
</div>


<link rel='stylesheet' href='/vendor/crudbooster/assets/select2/dist/css/select2.min.css'/>
<style type="text/css">
    .select2-container--default .select2-selection--single {
        border-radius: 0px !important
    }

    .select2-container .select2-selection--single {
        height: 35px
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #3c8dbc !important;
        border-color: #367fa9 !important;
        color: #fff !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src='/vendor/crudbooster/assets/select2/dist/js/select2.full.min.js' defer></script>

<script>
    $(document).ready(function() {
        $('.filterselect2').select2();
        setTimeout(() => {
            console.log($('.filterselect2'));
        }, 1000);
    });
</script>