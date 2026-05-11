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
        Systemever Feature
    </div>

    <div class="panel-body">

        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#home">Cloud quality performance</a></li>
            <li><a data-toggle="tab" href="#menu1">Security compliance</a></li>
            <li><a data-toggle="tab" href="#menu2">APAC CIOoutlook Top ERP 2018</a></li>
            <li><a data-toggle="tab" href="#menu3">APAC CIOoutlook Top ERP 2020</a></li>
        </ul>

        <form action="{{CRUDBooster::mainpath() . "/qualitysecurity/" . $lang}}" method="POST">
            @csrf
            <div class="tab-content">
                <div id="home" class="tab-pane fade in active">
                    <h3 class="text-center" style="margin-bottom:30px">Cloud Quality Performance</h3>

                    @include("admin/elements/upload", [
                        'label'         => "Cloud Quality Performance Logo",
                        'name'          => "json_content[cloud_quality_performance_logo]",
                        'placeholder'   => "Cloud Quality Performance Logo",
                        'value'         => old("json_content.cloud_quality_performance_logo", !empty($json_content->cloud_quality_performance_logo) ? $json_content->cloud_quality_performance_logo : url('image/default-pic.jpg')),
                    ])

                    @include("admin/elements/input", [
                        'label'         => "Cloud Quality Performance Title",
                        'name'          => "json_content[cloud_quality_performance_title]",
                        'placeholder'   => "Cloud Quality Performance Title",
                        'value'         => old("json_content.cloud_quality_performance_title", $json_content->cloud_quality_performance_title),
                    ])

                    @include("admin/elements/textarea", [
                        'label'         => "Cloud Quality Performance Detail",
                        'name'          => "json_content[cloud_quality_performance_detail]",
                        'placeholder'   => "Cloud Quality Performance Detail",
                        'value'         => old("json_content.cloud_quality_performance_detail", $json_content->cloud_quality_performance_detail),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Cloud Quality Service Name",
                        'name'          => "json_content[cloud_quality_performance_service_name]",
                        'placeholder'   => "Cloud Quality Service Name",
                        'value'         => old("json_content.cloud_quality_performance_service_name", $json_content->cloud_quality_performance_service_name),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                            'description'   => ['type' => 'textarea', 'label' => 'Description']
                        ]
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Cloud Quality Quality Performance Test",
                        'name'          => "json_content[cloud_quality_performance_performance_test]",
                        'placeholder'   => "Cloud Quality Quality Performance Test",
                        'value'         => old("json_content.cloud_quality_performance_performance_test", $json_content->cloud_quality_performance_performance_test),
                        'header'        => [
                            'icon'          => ['type' => 'file', 'label' => 'Icon'],
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                        ]
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Checking the management system",
                        'name'          => "json_content[cloud_quality_performance_management_system]",
                        'placeholder'   => "Checking the management system",
                        'value'         => old("json_content.cloud_quality_performance_management_system", $json_content->cloud_quality_performance_management_system),
                        'header'        => [
                            'image'         => ['type' => 'file', 'label' => 'Image'],
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                            'description'   => ['type' => 'textarea', 'label' => 'Description']
                        ]
                    ])

                </div>
                <div id="menu1" class="tab-pane fade">
                    <h3 class="text-center" style="margin-bottom:30px">Security Compliance</h3>

                    @include("admin/elements/input", [
                        'label'         => "Security Compliance Title",
                        'name'          => "json_content[security_compliance_title]",
                        'placeholder'   => "Security Compliance Title",
                        'value'         => old("json_content.security_compliance_title", $json_content->security_compliance_title),
                    ])

                    @include("admin/elements/input", [
                        'label'         => "Security Compliance Sub Title",
                        'name'          => "json_content[security_compliance_sub_title]",
                        'placeholder'   => "Security Compliance Sub Title",
                        'value'         => old("json_content.security_compliance_sub_title", $json_content->security_compliance_sub_title),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Azure Cloud Service Feature",
                        'name'          => "json_content[azure_cloud_service_feature]",
                        'placeholder'   => "Azure Cloud Service Feature",
                        'value'         => old("json_content.azure_cloud_service_feature", $json_content->azure_cloud_service_feature),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                            'description'   => ['type' => 'textarea', 'label' => 'Description']
                        ]
                    ])

                    @include("admin/elements/upload", [
                        'label'         => "Azure Compliance",
                        'name'          => "json_content[cloud_quality_azure_compliance]",
                        'placeholder'   => "Azure Compliance",
                        'value'         => old("json_content.cloud_quality_azure_compliance", !empty($json_content->cloud_quality_azure_compliance) ? $json_content->cloud_quality_azure_compliance : url('image/default-pic.jpg')),
                    ])

                    @include("admin/elements/upload", [
                        'label'         => "Azure Compliance Mobile",
                        'name'          => "json_content[cloud_quality_azure_compliance_mobile]",
                        'placeholder'   => "Azure Compliance Mobile",
                        'value'         => old("json_content.cloud_quality_azure_compliance_mobile", !empty($json_content->cloud_quality_azure_compliance_mobile) ? $json_content->cloud_quality_azure_compliance_mobile : url('image/default-pic.jpg')),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Azure Standard and Certification",
                        'name'          => "json_content[cloud_quality_azure_standard]",
                        'placeholder'   => "Azure Standard and Certification",
                        'value'         => old("json_content.cloud_quality_azure_standard", !empty($json_content->cloud_quality_azure_standard) ? $json_content->cloud_quality_azure_standard : []),
                        'header'        => [
                            'program'               => ['type' => 'input', 'label' => 'Program'],
                            'applicable_country'    => ['type' => 'input', 'label' => 'Applicable country'],
                            'type'                  => ['type' => 'input', 'label' => 'Type'],
                            'explanation'           => ['type' => 'input', 'label' => 'Explanation'],
                        ]
                    ])

                    @include("admin/elements/table", [
                        'label'         => "Microsoft Security Compliance Framework",
                        'name'          => "json_content[cloud_quality_microsoft_security]",
                        'placeholder'   => "Microsoft Security Compliance Framework",
                        'value'         => old("json_content.cloud_quality_microsoft_security", $json_content->cloud_quality_microsoft_security),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                        ]
                    ])

                </div>
                <div id="menu2" class="tab-pane fade">
                    <h3 class="text-center" style="margin-bottom:30px">APAC CIO Outlook Top ERP 2018</h3>

                    @include("admin/elements/upload", [
                        'label'         => "APAC CIO Outlook Top ERP 2018 Logo",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2018_logo]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2018 Logo",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2018_logo", !empty($json_content->apac_cio_outlook_top_erp_2018_logo) ? $json_content->apac_cio_outlook_top_erp_2018_logo : url('image/default-pic.jpg')),
                    ])

                    @include("admin/elements/input", [
                        'label'         => "APAC CIO Outlook Top ERP 2018 Title",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2018_title]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2018 Title",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2018_title", $json_content->apac_cio_outlook_top_erp_2018_title),
                    ])

                    @include("admin/elements/input", [
                        'label'         => "APAC CIO Outlook Top ERP 2018 Sub Title",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2018_sub_title]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2018 Sub Title",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2018_sub_title", $json_content->apac_cio_outlook_top_erp_2018_sub_title),
                    ])

                    @include("admin/elements/textarea", [
                        'label'         => "APAC CIO Outlook Top ERP 2018 Description",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2018_description]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2018 Description",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2018_description", $json_content->apac_cio_outlook_top_erp_2018_description),
                    ])
                    
                    @include("admin/elements/table", [
                        'label'         => "APAC CIO Outlook Top ERP 2018 Service Name",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2018_service_name]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2018 Service Name",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2018_service_name", $json_content->apac_cio_outlook_top_erp_2018_service_name),
                        'header'        => [
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                            'description'   => ['type' => 'textarea', 'label' => 'Description']
                        ]
                    ])

                    @include("admin/elements/table", [
                        'label'         => "APAC CIO Outlook Top ERP 2018 Features",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2018_features]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2018 Features",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2018_features", $json_content->apac_cio_outlook_top_erp_2018_features),
                        'header'        => [
                            'image'         => ['type' => 'file', 'label' => 'Image'],
                            'title'         => ['type' => 'input', 'label' => 'Title'],
                            'description'   => ['type' => 'textarea', 'label' => 'Description']
                        ]
                    ])
                </div>
                <div id="menu3" class="tab-pane fade">
                    <h3 class="text-center" style="margin-bottom:30px">APAC CIO Outlook Top ERP 2020</h3>

                    @include("admin/elements/upload", [
                        'label'         => "APAC CIO Outlook Top ERP 2020 Logo",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2020_logo]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2020 Logo",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2020_logo", !empty($json_content->apac_cio_outlook_top_erp_2020_logo) ? $json_content->apac_cio_outlook_top_erp_2020_logo : url('image/default-pic.jpg')),
                    ])

                    @include("admin/elements/input", [
                        'label'         => "APAC CIO Outlook Top ERP 2020 Title",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2020_title]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2020 Title",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2020_title", $json_content->apac_cio_outlook_top_erp_2020_title),
                    ])

                    @include("admin/elements/input", [
                        'label'         => "APAC CIO Outlook Top ERP 2020 Sub Title",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2020_sub_title]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2020 Sub Title",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2020_sub_title", $json_content->apac_cio_outlook_top_erp_2020_sub_title),
                    ])

                    @include("admin/elements/textarea", [
                        'label'         => "APAC CIO Outlook Top ERP 2020 Description",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2020_description]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2020 Description",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2020_description", $json_content->apac_cio_outlook_top_erp_2020_description),
                    ])

                    @include("admin/elements/table", [
                        'label'         => "APAC CIO Outlook Top ERP 2020 Features",
                        'name'          => "json_content[apac_cio_outlook_top_erp_2020_features]",
                        'placeholder'   => "APAC CIO Outlook Top ERP 2020 Features",
                        'value'         => old("json_content.apac_cio_outlook_top_erp_2020_features", $json_content->apac_cio_outlook_top_erp_2020_features),
                        'header'        => [
                            'description'   => ['type' => 'textarea', 'label' => 'Description']
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