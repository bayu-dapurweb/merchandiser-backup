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
        Pricing
    </div>

    <div class="panel-body">

        <div class="mb-3">
            <a href="?lang=EN" class="btn btn-{{get('lang', 'EN') == 'EN' ? 'success' : 'default'}}">EN</a>
            <a href="?lang=ID" class="btn btn-{{get('lang') == 'ID' ? 'success' : 'default'}}">ID</a>
        </div>

        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#home">Integrated ERP</a></li>
            <li><a data-toggle="tab" href="#menu1">Accounting and Tax Consulting Service</a></li>
        </ul>

        <form action="{{CRUDBooster::mainpath() . "/pricing?lang=" . get('lang', 'EN')}}" method="POST">
            @csrf
            <div class="tab-content">
                <div id="home" class="tab-pane fade in active">

                    <h3 class="text-center" style="margin-bottom:30px">i5 Trading and Distribution - Standard</h3>

                    @include("admin/elements/input", [
                        'label'         => "i5 Standard price",
                        'name'          => "json_content[i5_standard_price]",
                        'placeholder'   => "i5 Standard price",
                        'value'         => old("json_content.i5_standard_price", $json_content->i5_standard_price),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "i5 Standard User & Space",
                        'name'          => "json_content[i5_standard_user_space]",
                        'placeholder'   => "i5 Standard User & Space",
                        'value'         => old("json_content.i5_standard_user_space", $json_content->i5_standard_user_space),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ]) 
                    
                    @include("admin/elements/table", [
                        'label'         => "i5 Standard Feature",
                        'name'          => "json_content[i5_standard_feature]",
                        'placeholder'   => "i5 Standard Feature",
                        'value'         => old("json_content.i5_standard_feature", $json_content->i5_standard_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ])

                    <h3 class="text-center" style="margin-bottom:30px">i5 Trading and Distribution - Enterprice</h3>

                    @include("admin/elements/input", [
                        'label'         => "i5 Enterprice price",
                        'name'          => "json_content[i5_Enterprice_price]",
                        'placeholder'   => "i5 Enterprice price",
                        'value'         => old("json_content.i5_Enterprice_price", $json_content->i5_Enterprice_price),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "i5 Enterprice User & Space",
                        'name'          => "json_content[i5_Enterprice_user_space]",
                        'placeholder'   => "i5 Enterprice User & Space",
                        'value'         => old("json_content.i5_Enterprice_user_space", $json_content->i5_Enterprice_user_space),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ]) 

                    @include("admin/elements/table", [
                        'label'         => "i5 Enterprice Feature",
                        'name'          => "json_content[i5_Enterprice_feature]",
                        'placeholder'   => "i5 Enterprice Feature",
                        'value'         => old("json_content.i5_Enterprice_feature", $json_content->i5_Enterprice_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ])

                    <h3 class="text-center" style="margin-bottom:30px">i7 Manufacturing ERP - Standard</h3>

                    @include("admin/elements/input", [
                        'label'         => "i7 Standard price",
                        'name'          => "json_content[i7_standard_price]",
                        'placeholder'   => "i7 Standard price",
                        'value'         => old("json_content.i7_standard_price", $json_content->i7_standard_price),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "i7 Standard User & Space",
                        'name'          => "json_content[i7_standard_user_space]",
                        'placeholder'   => "i7 Standard User & Space",
                        'value'         => old("json_content.i7_standard_user_space", $json_content->i7_standard_user_space),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ]) 

                    @include("admin/elements/table", [
                        'label'         => "i7 Standard Feature",
                        'name'          => "json_content[i7_standard_feature]",
                        'placeholder'   => "i7 Standard Feature",
                        'value'         => old("json_content.i7_standard_feature", $json_content->i7_standard_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ])

                    <h3 class="text-center" style="margin-bottom:30px">i7 Manufacturing ERP - Enterprice</h3>

                    @include("admin/elements/input", [
                        'label'         => "i7 Enterprice price",
                        'name'          => "json_content[i7_Enterprice_price]",
                        'placeholder'   => "i7 Enterprice price",
                        'value'         => old("json_content.i7_Enterprice_price", $json_content->i7_Enterprice_price),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "i7 Enterprice User & Space",
                        'name'          => "json_content[i7_Enterprice_user_space]",
                        'placeholder'   => "i7 Enterprice User & Space",
                        'value'         => old("json_content.i7_Enterprice_user_space", $json_content->i7_Enterprice_user_space),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ]) 

                    @include("admin/elements/table", [
                        'label'         => "i7 Enterprice Feature",
                        'name'          => "json_content[i7_Enterprice_feature]",
                        'placeholder'   => "i7 Enterprice Feature",
                        'value'         => old("json_content.i7_Enterprice_feature", $json_content->i7_Enterprice_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ])



                </div>
                <div id="menu1" class="tab-pane fade">
                    <h3 class="text-center" style="margin-bottom:30px">i1 Accounting and Tax Consulting Service</h3>

                    @include("admin/elements/input", [
                        'label'         => "i1 price",
                        'name'          => "json_content[i1_price]",
                        'placeholder'   => "i1 price",
                        'value'         => old("json_content.i1_price", $json_content->i1_price),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Account Service Feature",
                        'name'          => "json_content[account_service_feature]",
                        'placeholder'   => "Account Service Feature",
                        'value'         => old("json_content.account_service_feature", $json_content->account_service_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ]) 

                    @include("admin/elements/table", [
                        'label'         => "Tax Service Feature",
                        'name'          => "json_content[tax_service_feature]",
                        'placeholder'   => "Tax Service Feature",
                        'value'         => old("json_content.tax_service_feature", $json_content->tax_service_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Support Software",
                        'name'          => "json_content[support_software]",
                        'placeholder'   => "Support Software",
                        'value'         => old("json_content.support_software", $json_content->support_software),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Feature'],
                        ]
                    ])

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