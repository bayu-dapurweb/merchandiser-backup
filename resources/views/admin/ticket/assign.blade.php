@extends('crudbooster::admin_template')
@section('content')
<style>
    .pt-4 {
        padding-top:40px;
    }

    .d-block {
        display: block;
    }

    .d-none {
        display: none !important;
    }

    .img-icon {
        width:60px;
        height:60px;
    }

    .text-center {
        text-align: center;
    }

    .m-0-auto {
        margin: 0 auto !important;
    }

    .no-float: {
        float:none !important;
    }

    .d-inline-block {
        display: inline-block;
    }

    .col {
        width:200px
    }

    .mb-3 {
        margin-bottom: 30px;
    }

    .nav .active a {
        background-color: rgb(121, 121, 121) !important;
        border:solid 1px silver;
        color:#fff !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-xs-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                Assign to Operator
            </div>
        
            <div class="panel-body">
        
                <form action="" method="post">
                    @csrf
                    <label for="user_id">Operator</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">-Select Operator-</option>
                        @foreach ($users as $v)
                        <option value="{{$v->id}}">{{$v->firstname}} {{$v->lastname}}</option>
                        @endforeach
                    </select><br>
                    <button class="btn btn-primary btn-block" type="submit">Submit</button>
                </form>
        
            </div>
        </div>
    </div>
</div>

@endsection