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
            <h3 class="text-center" style="margin-bottom:30px">Systemever</h3>

            @include("admin/elements/input", [
                'label'         => "Title",
                'name'          => "json_content[title]",
                'placeholder'   => "Title",
                'value'         => old("json_content.title", $json_content->title),
            ])

            @include("admin/elements/textarea", [
                'label'         => "Sub Title Detail",
                'name'          => "json_content[sub_title]",
                'placeholder'   => "Sub Title Detail",
                'value'         => old("json_content.sub_title", $json_content->sub_title),
            ])

            @include("admin/elements/upload", [
                'label'         => "Map photo",
                'name'          => "json_content[map_photo]",
                'placeholder'   => "Map photo",
                'value'         => old("json_content.map_photo", !empty($json_content->map_photo) ? $json_content->map_photo : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/textarea", [
                'label'         => "Bold Text Detail",
                'name'          => "json_content[bold_text_detail]",
                'placeholder'   => "Bold Text Detail",
                'value'         => old("json_content.bold_text_detail", $json_content->bold_text_detail),
            ])

            @include("admin/elements/textarea", [
                'label'         => "Regular Text Detail",
                'name'          => "json_content[regular_text_detail]",
                'placeholder'   => "Regular Text Detail",
                'value'         => old("json_content.regular_text_detail", $json_content->regular_text_detail),
            ])

            @include("admin/elements/upload", [
                'label'         => "Vision Photo",
                'name'          => "json_content[vision_photo]",
                'placeholder'   => "Vision Photo",
                'value'         => old("json_content.vision_photo", !empty($json_content->vision_photo) ? $json_content->vision_photo : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/input", [
                'label'         => "Vision Detail",
                'name'          => "json_content[vision_detail]",
                'placeholder'   => "Vision Detail",
                'value'         => old("json_content.vision_detail", $json_content->vision_detail),
            ])

            @include("admin/elements/textarea", [
                'label'         => "Mission",
                'name'          => "json_content[mission]",
                'placeholder'   => "Mission",
                'value'         => old("json_content.mission", $json_content->mission),
            ])

            {{-- @include("admin/elements/table", [
                'label'         => "Country Counter",
                'name'          => "json_content[country_counter]",
                'placeholder'   => "Country Counter",
                'value'         => old("json_content.country_counter", $json_content->country_counter),
                'header'        => [
                    'icon'          => ['type' => 'file', 'label' => 'Icon'],
                    'country'       => ['type' => 'input', 'label' => 'Country'],
                    'counter'       => ['type' => 'input', 'label' => 'Counter']
                ]
            ]) --}}

            {{-- @include("admin/elements/table", [
                'label'         => "Photos",
                'name'          => "json_content[photos]",
                'placeholder'   => "Photos",
                'value'         => old("json_content.photos", $json_content->photos),
                'header'        => [
                    'photo'         => ['type' => 'file', 'label' => 'Photo'],
                    'title'         => ['type' => 'input', 'label' => 'Title'],
                ]
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