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
</style>

<div class="panel panel-default">
    <div class="panel-heading">
        Systemever Feature
    </div>

    <div class="panel-body">
        <form action="{{CRUDBooster::mainpath() . "/contentdetail/" . $title . "/" . $lang}}" method="POST">
            @csrf
            <h3 class="text-center" style="margin-bottom:30px">History</h3>

            @include("admin/elements/textarea", [
                'label'         => "Title Detail",
                'name'          => "json_content[title_detail]",
                'placeholder'   => "Title Detail",
                'value'         => old("json_content.title_detail", $json_content->title_detail),
            ])

            <div class="form-group row" style="padding-top:5px;">
                <label class="control-label col-sm-3" style="padding-top:5px; text-align:right">About History<span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <a class="btn btn-primary" href="{{ uri('admin/about_us_histories') }}">About History Table</a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <button class="btn btn-primary" type="submit">{{ stringlang('Submit', 'Kirim') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection