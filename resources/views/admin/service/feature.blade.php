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
        <form action="{{CRUDBooster::mainpath() . "/systemeverfeature/" . $lang}}" method="POST">
            @csrf
            <h3 class="text-center" style="margin-bottom:30px">{{ stringlang('Unique Selling Point', 'Kelebihan Produk') }}</h3>
            @include("admin/elements/input", [
                'label'         => "Youtube URL",
                'name'          => "json_content[unique_selling_point_youtube]",
                'placeholder'   => "Input Youtube URL",
                'value'         => old("json_content.unique_selling_point_youtube", $json_content->unique_selling_point_youtube),
            ])
            @include("admin/elements/multiple-upload", [
                'label'         => "Multiple Banner Image",
                'name'          => "json_content[unique_selling_point_image]",
                'placeholder'   => "Image Upload",
                'value'         => old("json_content.unique_selling_point_image", !empty($json_content->unique_selling_point_image) ? $json_content->unique_selling_point_image : [url('image/default-pic.jpg')]),
            ])

            <h3 class="text-center" style="margin-bottom:30px">Visual K Proccess Map</h3>

            @include("admin/elements/input", [
                'label'         => "Proccess Map Feature 1",
                'name'          => "json_content[visual_k_proccess_map_feature_1]",
                'placeholder'   => "Proccess Map Feature 1",
                'value'         => old("json_content.visual_k_proccess_map_feature_1", $json_content->visual_k_proccess_map_feature_1),
            ])

            @include("admin/elements/input", [
                'label'         => "Proccess Map Feature 2",
                'name'          => "json_content[visual_k_proccess_map_feature_2]",
                'placeholder'   => "Proccess Map Feature 2",
                'value'         => old("json_content.visual_k_proccess_map_feature_2", $json_content->visual_k_proccess_map_feature_2),
            ])

            @include("admin/elements/upload", [
                'label'         => "Proccess Map Banner",
                'name'          => "json_content[visual_k_proccess_map_banner]",
                'placeholder'   => "Proccess Map Banner",
                'value'         => old("json_content.visual_k_proccess_map_banner", !empty($json_content->visual_k_proccess_map_banner) ? $json_content->visual_k_proccess_map_banner : url('image/default-pic.jpg')),
            ])

            {{-- @include("admin/elements/upload", [
                'label'         => "Proccess Map Banner Mobile",
                'name'          => "json_content[visual_k_proccess_map_banner_mobile]",
                'placeholder'   => "Proccess Map Banner Mobile",
                'value'         => old("json_content.visual_k_proccess_map_banner_mobile", !empty($json_content->visual_k_proccess_map_banner_mobile) ? $json_content->visual_k_proccess_map_banner_mobile : url('image/default-pic.jpg')),
            ]) --}}

            <h3 class="text-center" style="margin-bottom:30px">Visual K Superiority</h3>

            @include("admin/elements/upload", [
                'label'         => "Superiority Banner",
                'name'          => "json_content[visual_k_superiority_map_banner]",
                'placeholder'   => "Superiority Banner",
                'value'         => old("json_content.visual_k_superiority_map_banner", !empty($json_content->visual_k_superiority_map_banner) ? $json_content->visual_k_superiority_map_banner : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/table", [
                'label'         => "Superiority List",
                'name'          => "json_content[visual_k_superiority_map_list]",
                'placeholder'   => "Superiority List",
                'value'         => old("json_content.visual_k_superiority_map_list", $json_content->visual_k_superiority_map_list),
                'header'        => [
                    'icon'          => ['type' => 'file', 'label' => 'Icon'],
                    'title'         => ['type' => 'input', 'label' => 'Title'],
                    'description'   => ['type' => 'textarea', 'label' => 'Description']
                ]
            ])

            <h3 class="text-center" style="margin-bottom:30px">Mobile App Featured</h3>

            @include("admin/elements/input", [
                'label'         => "Mobile App Description",
                'name'          => "json_content[mobile_app_description]",
                'placeholder'   => "Mobile App Description",
                'value'         => old("json_content.mobile_app_description", $json_content->mobile_app_description),
            ])

            @include("admin/elements/upload", [
                'label'         => "Mobile App Ilustration",
                'name'          => "json_content[mobile_app_ilustration]",
                'placeholder'   => "Mobile App Ilustration",
                'value'         => old("json_content.mobile_app_ilustration", !empty($json_content->mobile_app_ilustration) ? $json_content->mobile_app_ilustration : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/table", [
                'label'         => "Mobile App Selling Point",
                'name'          => "json_content[mobile_app_selling_point]",
                'placeholder'   => "Mobile App Selling Point",
                'value'         => old("json_content.mobile_app_selling_point", $json_content->mobile_app_selling_point),
                'header'        => [
                    'icon'          => ['type' => 'file', 'label' => 'Icon'],
                    'title'         => ['type' => 'input', 'label' => 'Title'],
                ]
            ])

            @include("admin/elements/upload", [
                'label'         => "Mobile App Bottom Ilustration 1",
                'name'          => "json_content[mobile_app_bottom_ilustration_1]",
                'placeholder'   => "Mobile App Bottom Ilustration 1",
                'value'         => old("json_content.mobile_app_bottom_ilustration_1", !empty($json_content->mobile_app_bottom_ilustration_1) ? $json_content->mobile_app_bottom_ilustration_1 : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/upload", [
                'label'         => "Mobile App Bottom Ilustration 2",
                'name'          => "json_content[mobile_app_bottom_ilustration_2]",
                'placeholder'   => "Mobile App Bottom Ilustration 2",
                'value'         => old("json_content.mobile_app_bottom_ilustration_2", !empty($json_content->mobile_app_bottom_ilustration_2) ? $json_content->mobile_app_bottom_ilustration_2 : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/table", [
                'label'         => "Mobile App Supperiority",
                'name'          => "json_content[mobile_app_superiority]",
                'placeholder'   => "Mobile App Supperiority",
                'value'         => old("json_content.mobile_app_superiority", $json_content->mobile_app_superiority),
                'header'        => [
                    'title' => ['type' => 'input', 'label' => 'Title'],
                ]
            ])

            <h3 class="text-center" style="margin-bottom:30px">Multilanguage Featured</h3>

            @include("admin/elements/upload", [
                'label'         => "Multilanguage Feature",
                'name'          => "json_content[mobile_app_multilanguage_feature]",
                'placeholder'   => "Multilanguage Feature",
                'value'         => old("json_content.mobile_app_multilanguage_feature", !empty($json_content->mobile_app_multilanguage_feature) ? $json_content->mobile_app_multilanguage_feature : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/upload", [
                'label'         => "Multilanguage Feature Mobile",
                'name'          => "json_content[mobile_app_multilanguage_feature_mobile]",
                'placeholder'   => "Multilanguage Feature Mobile",
                'value'         => old("json_content.mobile_app_multilanguage_feature_mobile", !empty($json_content->mobile_app_multilanguage_feature_mobile) ? $json_content->mobile_app_multilanguage_feature_mobile : url('image/default-pic.jpg')),
            ])

            <h3 class="text-center" style="margin-bottom:30px">Multi Curency</h3>

            @include("admin/elements/input", [
                'label'         => "Multi Curency Feature Description",
                'name'          => "json_content[mobile_app_multicurency_description]",
                'placeholder'   => "Multi Curency Feature Description",
                'value'         => old("json_content.mobile_app_multicurency_description", $json_content->mobile_app_multicurency_description),
            ])

            @include("admin/elements/upload", [
                'label'         => "Multi Curency Feature Ilustration",
                'name'          => "json_content[mobile_app_multi_curency_feature_ilustration]",
                'placeholder'   => "Multi Curency Feature Ilustration",
                'value'         => old("json_content.mobile_app_multi_curency_feature_ilustration", !empty($json_content->mobile_app_multi_curency_feature_ilustration) ? $json_content->mobile_app_multi_curency_feature_ilustration : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/upload", [
                'label'         => "Multi Curency Feature Ilustration Mobile",
                'name'          => "json_content[mobile_app_multi_curency_feature_ilustration_mobile]",
                'placeholder'   => "Multi Curency Feature Ilustration Mobile",
                'value'         => old("json_content.mobile_app_multi_curency_feature_ilustration_mobile", !empty($json_content->mobile_app_multi_curency_feature_ilustration_mobile) ? $json_content->mobile_app_multi_curency_feature_ilustration_mobile : url('image/default-pic.jpg')),
            ])

            {{-- @include("admin/elements/summernote", [
                'label'         => "Mobile Apps",
                'name'          => "json_content[visual_k_featured]",
                'placeholder'   => "Mobile Apps",
                'value'         => old("json_content.visual_k_featured", base64_decode($json_content->visual_k_featured)),
            ]) --}}

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