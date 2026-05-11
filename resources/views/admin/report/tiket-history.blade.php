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
        Ticket History Report
    </div>

    <div class="panel-body">
        <div class="filter">
            <label>Filter</label>
            <form action="" method="get" class="form-inline">
                <input name="job_number" type="text" placeholder="Job No" class="form-control" value="{{ get('jobno') }}">
                <input name="subject" type="text" placeholder="Subject" class="form-control" value="{{ get('subject') }}">
                <input name="datestart" type="text" placeholder="Date Start" class="form-control" value="{{ get('datestart', date("Y-m-01")) }}">
                <input name="datend" type="text" placeholder="Date End" class="form-control" value="{{ get('datend', date("Y-m-d")) }}">
                <button type="submit" class="btn btn-primary">Search</button>
                <button class="btn btn-success" name="export" value="1">Excel Export</button>
            </form>
        </div>
        <br>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <td>Job Number</td>
                        <td>Subject</td>
                        <td>Ticket Categories</td>
                        <td>Urgency</td>
                        <td>Ticket Status</td>
                        <td>Date</td>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($data))
                        @foreach ($data as $v)
                        <tr>
                            <td>{{  $v->job_number }}</td>
                            <td>{{  $v->subject }}</td>
                            <td>{{  $v->categories->title }}</td>
                            <td>{{  ucfirst($v->urgency_level) }}</td>
                            <td>{{  ucfirst($v->tiket_status) }}</td>
                            <td>{{  $v->created_at }}</td>
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