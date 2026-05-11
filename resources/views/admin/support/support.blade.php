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
        Support Guide
    </div>

    <div class="panel-body">

        <a href="?lang=EN" class="btn btn-{{get('lang', 'EN') == 'EN' ? 'success' : 'default'}}">EN</a>
        <a href="?lang=ID" class="btn btn-{{get('lang') == 'ID' ? 'success' : 'default'}}">ID</a>

        <form action="{{CRUDBooster::mainpath() . "/supportguide"}}?lang={{get('lang', 'EN')}}" method="POST">
            @csrf
            
            <h3 class="text-center" style="margin-bottom:30px">Introduction Inquiry</h3>

            @include("admin/elements/upload", [
                'label'         => "Introduction Inquiry Icon",
                'name'          => "json_content[introduction_inquiry_icon]",
                'placeholder'   => "Introduction Inquiry Icon",
                'value'         => old("json_content.introduction_inquiry_icon", !empty($json_content->introduction_inquiry_icon) ? $json_content->introduction_inquiry_icon : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/input", [
                'label'         => "Introduction Inquiry Title",
                'name'          => "json_content[introduction_inquiry_title]",
                'placeholder'   => "Introduction Inquiry Title",
                'value'         => old("json_content.introduction_inquiry_title", $json_content->introduction_inquiry_title),
            ])

            @include("admin/elements/table", [
                'label'         => "Introduction Inquiry Info",
                'name'          => "json_content[introduction_inquiry_info]",
                'placeholder'   => "Introduction Inquiry Info",
                'value'         => old("json_content.introduction_inquiry_info", $json_content->introduction_inquiry_info),
                'header'        => [
                    'link'         => ['type' => 'select', 'label' => 'Link Type', 'options' => ['email', 'phone', 'href', 'none']],
                    'icon'         => ['type' => 'file', 'label' => 'Icon'],
                    'input'        => ['type' => 'input', 'label' => 'Information'],
                ]
            ]) 

            <h3 class="text-center" style="margin-bottom:30px">Connection Failure</h3>

            @include("admin/elements/upload", [
                'label'         => "Connection Failure Icon",
                'name'          => "json_content[connection_failure_icon]",
                'placeholder'   => "Connection Failure Icon",
                'value'         => old("json_content.connection_failure_icon", !empty($json_content->connection_failure_icon) ? $json_content->connection_failure_icon : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/input", [
                'label'         => "Connection Failure Title",
                'name'          => "json_content[connection_failure_title]",
                'placeholder'   => "Connection Failure Title",
                'value'         => old("json_content.connection_failure_title", $json_content->connection_failure_title),
            ])

            @include("admin/elements/table", [
                'label'         => "Connection Failure Info",
                'name'          => "json_content[connection_failure_info]",
                'placeholder'   => "Connection Failure Info",
                'value'         => old("json_content.connection_failure_info", $json_content->connection_failure_info),
                'header'        => [
                    'link'         => ['type' => 'select', 'label' => 'Link Type', 'options' => ['email', 'phone', 'href', 'none']],
                    'icon'         => ['type' => 'file', 'label' => 'Icon'],
                    'input'        => ['type' => 'input', 'label' => 'Information'],
                ]
            ]) 

            <h3 class="text-center" style="margin-bottom:30px">Functional Improvement</h3>
            
            @include("admin/elements/upload", [
                'label'         => "Functional Improvement Icon",
                'name'          => "json_content[functional_improvement_icon]",
                'placeholder'   => "Functional Improvement Icon",
                'value'         => old("json_content.functional_improvement_icon", !empty($json_content->functional_improvement_icon) ? $json_content->functional_improvement_icon : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/input", [
                'label'         => "Functional Improvement Title",
                'name'          => "json_content[functional_improvement_title]",
                'placeholder'   => "Functional Improvement Title",
                'value'         => old("json_content.functional_improvement_title", $json_content->functional_improvement_title),
            ])

            @include("admin/elements/table", [
                'label'         => "Functional Improvement Info",
                'name'          => "json_content[functional_improvement_info]",
                'placeholder'   => "Functional Improvement Info",
                'value'         => old("json_content.functional_improvement_info", $json_content->functional_improvement_info),
                'header'        => [
                    'link'         => ['type' => 'select', 'label' => 'Link Type', 'options' => ['email', 'phone', 'href', 'none']],
                    'icon'         => ['type' => 'file', 'label' => 'Icon'],
                    'input'        => ['type' => 'input', 'label' => 'Information'],
                    'image'        => ['type' => 'file', 'label' => 'Image'],
                ]
            ]) 

            <h3 class="text-center" style="margin-bottom:30px">Education Inquiry</h3>

            @include("admin/elements/upload", [
                'label'         => "Education Inquiry Icon",
                'name'          => "json_content[education_inquiry_icon]",
                'placeholder'   => "Education Inquiry Icon",
                'value'         => old("json_content.education_inquiry_icon", !empty($json_content->education_inquiry_icon) ? $json_content->education_inquiry_icon : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/input", [
                'label'         => "Education Inquiry Title",
                'name'          => "json_content[education_inquiry_title]",
                'placeholder'   => "Education Inquiry Title",
                'value'         => old("json_content.education_inquiry_title", $json_content->education_inquiry_title),
            ])

            @include("admin/elements/table", [
                'label'         => "Education Inquiry Info",
                'name'          => "json_content[education_inquiry_info]",
                'placeholder'   => "Education Inquiry Info",
                'value'         => old("json_content.education_inquiry_info", $json_content->education_inquiry_info),
                'header'        => [
                    'link'         => ['type' => 'select', 'label' => 'Link Type', 'options' => ['email', 'phone', 'href', 'none']],
                    'icon'         => ['type' => 'file', 'label' => 'Icon'],
                    'input'        => ['type' => 'input', 'label' => 'Information'],
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