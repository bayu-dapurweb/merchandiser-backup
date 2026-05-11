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

<div class="panel panel-default">
    <div class="panel-heading">
        Engineering Report
    </div>

    <div class="panel-body">
        <div class="filter">
            <label>Filter</label>
            <form action="" method="get" class="form-inline">
                <select name="ticket_categories_id" id="ticket_categories_id" class="form-control">
                    <option value="">All</option>
                    @foreach ($categories_option as $v) 
                    <option value="{{$v['id']}}" {{ get('ticket_categories_id') == $v['id'] ? "selected" : "" }}>{!!$v['name']!!}</option>
                    @endforeach
                </select>
                <input name="subject" type="text" placeholder="Subject" class="form-control" value="{{ get('subject') }}">
                <input name="datestart" type="date" placeholder="Date Start" class="form-control" value="{{ get('datestart', date("Y-m-01")) }}">
                <input name="datend" type="date" placeholder="Date End" class="form-control" value="{{ get('datend', date("Y-m-d")) }}">
                <button type="submit" class="btn btn-primary">Search</button>
                <button class="btn btn-success" name="export" value="1">Excel Export</button>
            </form>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <td>Date</td>
                        <td>Operator</td>
                        <td>Corective Action</td>
                        <td>Mesin</td>
                        <td>Nomor Mesin</td>
                        <td>Kendala</td>
                        <td>Status Perbaikan</td>
                        <td>Action </td>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($data))
                        @foreach ($data as $v)
                        <tr>
                            <td>{{  $v->created_at }}</td>
                            <td>{{  $v->operator->firstname . " " . $v->operator->firstname }}</td>
                            <td>{{  $v->subject }}</td>
                            <td>{{  ($v->machine->name) }}</td>
                            <td>{{  ($v->machine->code) }}</td>
                            <td>{{  $v->description }}</td>
                            <td>{{  ucfirst($v->tiket_status) }}</td>
                            <td>{{  ucfirst($v->categories->title) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @if (!empty($data) && count($data) > 0)
        {{ $data->appends(request()->query())->links() }}
        @endif
    </div>
</div>

@endsection