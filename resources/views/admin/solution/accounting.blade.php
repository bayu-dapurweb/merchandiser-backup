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
        <form action="{{CRUDBooster::mainpath() . "/accountingtaxconsultingservice/" . $id}}" method="POST">
            @csrf
            <h3 class="text-center" style="margin-bottom:30px">Accounting & Tax Consulting Service</h3>
            @include("admin/elements/input", [
                'label'         => "Title",
                'name'          => "json_content[title]",
                'placeholder'   => "Title",
                'value'         => old("json_content.title", $json_content->title),
            ])
            @include("admin/elements/input", [
                'label'         => "Sub Title",
                'name'          => "json_content[sub_title]",
                'placeholder'   => "Sub Title",
                'value'         => old("json_content.sub_title", $json_content->sub_title),
            ])
            @include("admin/elements/table", [
                'label'         => "Why Accounting & Tax Consulting Services",
                'name'          => "json_content[why]",
                'placeholder'   => "Why Accounting & Tax Consulting Services",
                'value'         => old("json_content.why", $json_content->why),
                'header'        => [
                    'description'   => ['type' => 'textarea', 'label' => 'Description']
                ]
            ])
            @include("admin/elements/upload", [
                'label'         => "Why Accounting & Tax Consulting Services Ilustration",
                'name'          => "json_content[why_ilustration]",
                'placeholder'   => "Why Accounting & Tax Consulting Services Ilustration",
                'value'         => old("json_content.why_ilustration", !empty($json_content->why_ilustration) ? $json_content->why_ilustration : url('image/default-pic.jpg')),
            ])
            @include("admin/elements/table", [
                'label'         => "How does it work?",
                'name'          => "json_content[how]",
                'placeholder'   => "How does it work?",
                'value'         => old("json_content.how", $json_content->how),
                'header'        => [
                    'description'   => ['type' => 'textarea', 'label' => 'Description']
                ]
            ])
            @include("admin/elements/table", [
                'label'         => "Benefits",
                'name'          => "json_content[benefits]",
                'placeholder'   => "Benefits",
                'value'         => old("json_content.benefits", $json_content->benefits),
                'header'        => [
                    'icon'   => ['type' => 'file', 'label' => 'Icon'],
                    'title'   => ['type' => 'input', 'label' => 'Title'],
                    'description'   => ['type' => 'textarea', 'label' => 'Description']
                ]
            ])
            @include("admin/elements/table", [
                'label'         => "Accounting Services​",
                'name'          => "json_content[accounting_service]",
                'placeholder'   => "Accounting Services​",
                'value'         => old("json_content.accounting_service", $json_content->accounting_service),
                'header'        => [
                    'title'   => ['type' => 'input', 'label' => 'Point'],
                ]
            ])
            @include("admin/elements/table", [
                'label'         => "Tax Reporting Services",
                'name'          => "json_content[tax_reporting_service]",
                'placeholder'   => "Tax Reporting Services",
                'value'         => old("json_content.tax_reporting_service", $json_content->tax_reporting_service),
                'header'        => [
                    'title'   => ['type' => 'input', 'label' => 'Point'],
                ]
            ])

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