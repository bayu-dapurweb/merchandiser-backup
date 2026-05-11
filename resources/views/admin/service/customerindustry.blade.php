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
        <form action="{{CRUDBooster::mainpath() . "/customerindustry/" . $lang}}" method="POST">
            @csrf
            <h3 class="text-center" style="margin-bottom:30px">{{ stringlang('Customer', 'Pelanggan') }}</h3>

            @include("admin/elements/upload", [
                'label'         => "Customer Map",
                'name'          => "json_content[customer_map]",
                'placeholder'   => "Customer Map",
                'value'         => old("json_content.customer_map", !empty($json_content->customer_map) ? $json_content->customer_map : url('image/default-pic.jpg')),
            ]) 
            
            @include("admin/elements/upload", [
                'label'         => "Customer Map Mobile",
                'name'          => "json_content[customer_map_mobile]",
                'placeholder'   => "Customer Map Mobile",
                'value'         => old("json_content.customer_map_mobile", !empty($json_content->customer_map_mobile) ? $json_content->customer_map_mobile : url('image/default-pic.jpg')),
            ]) 

            @include("admin/elements/table", [
                'label'         => "Country Counter",
                'name'          => "json_content[country_counter]",
                'placeholder'   => "Country Counter",
                'value'         => old("json_content.country_counter", $json_content->country_counter),
                'header'        => [
                    'icon'          => ['type' => 'file', 'label' => 'Icon'],
                    'country'       => ['type' => 'input', 'label' => 'Country'],
                    'counter'       => ['type' => 'input', 'label' => 'Counter']
                ]
            ])

            @include("admin/elements/table", [
                'label'         => "Suitable Industries",
                'name'          => "json_content[suitable_insdustries]",
                'placeholder'   => "Suitable Industries",
                'value'         => old("json_content.suitable_insdustries", $json_content->suitable_insdustries),
                'header'        => [
                    'icon'          => ['type' => 'file', 'label' => 'Icon'],
                    'title'         => ['type' => 'input', 'label' => 'Title'],
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