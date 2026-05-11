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
        Machine Report
    </div>

    <div class="panel-body">
        <div class="filter">
            <label>Filter</label>
            <form action="" method="get" class="form-inline">
                <input name="code" type="text" placeholder="Code" class="form-control" value="{{ get('code') }}">
                <input name="name" type="text" placeholder="Machine Name" class="form-control" value="{{ get('name') }}">
                <button type="submit" class="btn btn-primary">Search</button>
                <button class="btn btn-success" name="export" value="1">Excel Export</button>
            </form>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <td>Code</td>
                        <td>Machine Name</td>
                        <td>Ticket Count</td>
                        <td>Ticket Related</td>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($machines))
                        @foreach ($machines as $v)
                        <tr>
                            <td>{{  $v->code }}</td>
                            <td>{{  $v->name }}</td>
                            <td>{{  $v->ticket_count }}</td>
                            <td>
                                <a href="{{ url('admin/tickets?machines_id=' . $v->id) }}" class="btn btn-primary btn-xs">
                                    <i class="fa fa-ticket"></i>&nbsp;
                                    Go To Ticket
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @if (!empty($machines))
        {{ $machines->appends(request()->query())->links() }}
        @endif
    </div>
</div>

@endsection