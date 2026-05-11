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
            <h3 class="text-center" style="margin-bottom:30px">Indonesian Branch</h3>

            @include("admin/elements/upload", [
                'label'         => "Chief in Representative Indonesia Photo",
                'name'          => "json_content[CRI_photo]",
                'placeholder'   => "Chief in Representative Indonesia Photo",
                'value'         => old("json_content.CRI_photo", !empty($json_content->CRI_photo) ? $json_content->CRI_photo : url('image/default-pic.jpg')),
            ])

            @include("admin/elements/input", [
                'label'         => "Chief in Representative Indonesia Name",
                'name'          => "json_content[CRI_name]",
                'placeholder'   => "Chief in Representative Indonesia Name",
                'value'         => old("json_content.CRI_name", $json_content->CRI_name),
            ])

            @include("admin/elements/textarea", [
                'label'         => "Green Text Detail",
                'name'          => "json_content[green_text_detail]",
                'placeholder'   => "Green Text Detail",
                'value'         => old("json_content.green_text_detail", $json_content->green_text_detail),
            ])

            @include("admin/elements/textarea", [
                'label'         => "Regular Text Detail",
                'name'          => "json_content[regular_text_detail]",
                'placeholder'   => "Regular Text Detail",
                'value'         => old("json_content.regular_text_detail", $json_content->regular_text_detail),
            ])

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
                <label class="control-label col-sm-3" style="padding-top:5px; text-align:right">Photo<span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <table border="1" width="100%">
                        <tr>
                            <td>

                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[photo_grid_item]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.photo_grid_item", ($json_content->photo_grid_item)),
                                ])
                            </td>
                            <td rowspan="2">
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width2]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width2", ($json_content->grid_item_width2)),
                                ])
                            </td>
                            <td>
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width3]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width3", ($json_content->grid_item_width3)),
                                ])
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[photo_grid_item_2]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.photo_grid_item_2", ($json_content->photo_grid_item_2)),
                                ])
                            </td>
                            <td>
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width3_2]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width3_2", ($json_content->grid_item_width3_2)),
                                ])
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width4]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width4", ($json_content->grid_item_width4)),
                                ])
                            </td>
                            <td rowspan="2">
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width5]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width5", ($json_content->grid_item_width5)),
                                ])
                            </td>
                            <td rowspan="2">
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width6]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width6", ($json_content->grid_item_width6)),
                                ])
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width7]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width7", ($json_content->grid_item_width7)),
                                ])
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width8]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width8", ($json_content->grid_item_width8)),
                                ])
                            </td>
                            <td colspan="2">
                                @include("admin/elements/upload_title", [
                                    'label'         => "",
                                    'name'          => "json_content[grid_item_width8_2]",
                                    'placeholder'   => "",
                                    'value'         => old("json_content.grid_item_width8_2", ($json_content->grid_item_width8_2)),
                                ])
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            
            <div class="row" style="padding-top:30px;">
                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <button class="btn btn-primary" type="submit">{{ stringlang('Submit', 'Kirim') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection