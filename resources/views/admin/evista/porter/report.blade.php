@extends('crudbooster::admin_template')
@section('content')

@include("admin/elements/filter", [
     "param" => [
        ["type" => "date", "label" => "Priode Start", "name" => "datestart", "value" => get("datestart", date("Y-m-d"))],
        ["type" => "date", "label" => "Periode End", "name" => "dateend", "value" =>  get("dateend", date("Y-m-d"))],
        ["type" => "input", "label" => "Search", "name" => "title", "value" =>  get("title")],
    ],
    "with_export" => false
])

<div class="">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Report
                </div>
            
                <div class="panel-body">
                    <table class="table table-stripe">
                        <thead>
                            <tr>
                                <td>Name</td>
                                <td>Voucher Code</td>
                                <td>Fee Total</td>
                            </tr>
                        </thead>
                        @foreach ($penghasilan_poterer as $v)
                        <tr>
                            <td>{{ $v->promos->title }}</td>
                            <td>{{ $v->promos->voucher_code }}</td>
                            <td>{{ nominal($v->total_fee) }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>


@endsection