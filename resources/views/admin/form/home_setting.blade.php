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
</style>

<div class="panel panel-default">
    <div class="panel-heading">
          Homepage Setting
    </div>

    <div class="panel-body">
        <form class="" method="POST" action="{{CRUDBooster::mainpath()}}/setting">
            @csrf

            {{-- Introducing --}}

            <div class="row">
                <div class="col-sm-12">
                    <h4>Introducing</h4>
                    <hr>
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'intro_title', 'label' => 'Title'])
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'intro_sub_title', 'label' => 'Sub Title'])
                </div>
            </div>
            <div class="row pt-4">
                <div class="col-sm-6">
                    @include('admin/form/text-input', ['name' => 'intro_youtube_url', 'label' => 'Youtube URL'])
                </div>
                <div class="col-sm-6">
                    @include('admin/form/image-form', ['name' => 'intro_featured_image', 'label' => 'Featured Image'])
                </div>
            </div>
            <div class="row pt-4">
                <div class="col-sm-4">
                    @include('admin/form/icon-form', ['name' => 'home_intro_icon_1', 'name_label' => 'home_intro_icon_label_1'])
                </div>
            
                <div class="col-sm-4">
                    @include('admin/form/icon-form', ['name' => 'home_intro_icon_2', 'name_label' => 'home_intro_icon_label_2'])
                </div>
            
                <div class="col-sm-4">
                    @include('admin/form/icon-form', ['name' => 'home_intro_icon_3', 'name_label' => 'home_intro_icon_label_3'])
                </div>
            </div>

            {{-- Product / Solution --}}

            <div class="row pt-4">
                <div class="col-sm-12">
                    <h4>Product / Solution</h4>
                    <hr>
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'solution_title', 'label' => 'Title'])
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'solution_sub_title', 'label' => 'Sub Title'])
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-sm-6">
                    @include('admin/form/text-input', ['name' => 'i1_solution_title', 'label' => 'Title for i1'])
                    @include('admin/form/text-input', ['name' => 'i1_solution_sub_title', 'label' => 'Sub Title for i1'])
                    @include('admin/form/text-input', ['name' => 'i1_solution_sort_description', 'label' => 'Sort Decription for i1'])
                    <hr>
                    @include('admin/form/text-input', ['name' => 'i1_solution_point_1', 'label' => 'Selling point'])
                    @include('admin/form/text-input', ['name' => 'i1_solution_point_2', 'label' => 'Selling point'])
                    @include('admin/form/text-input', ['name' => 'i1_solution_point_3', 'label' => 'Selling point'])
                </div>
                <div class="col-sm-6">
                    @include('admin/form/image-form', ['name' => 'i1_featured_image', 'label' => 'Featured Image i1'])
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-sm-6">
                    @include('admin/form/image-form', ['name' => 'i5_featured_image', 'label' => 'Featured Image i5'])
                </div>
                <div class="col-sm-6">
                    @include('admin/form/text-input', ['name' => 'i5_solution_title', 'label' => 'Title for i5'])
                    @include('admin/form/text-input', ['name' => 'i5_solution_sub_title', 'label' => 'Sub Title for i5'])
                    @include('admin/form/text-input', ['name' => 'i5_solution_sort_description', 'label' => 'Sort Decription for i5'])
                    <hr>
                    @include('admin/form/text-input', ['name' => 'i5_solution_point_1', 'label' => 'Selling point'])
                    @include('admin/form/text-input', ['name' => 'i5_solution_point_2', 'label' => 'Selling point'])
                    @include('admin/form/text-input', ['name' => 'i5_solution_point_3', 'label' => 'Selling point'])
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-sm-6">
                    @include('admin/form/text-input', ['name' => 'i3_solution_title', 'label' => 'Title for i3'])
                    @include('admin/form/text-input', ['name' => 'i3_solution_sub_title', 'label' => 'Sub Title for i3'])
                    @include('admin/form/text-input', ['name' => 'i3_solution_sort_description', 'label' => 'Sort Decription for i3'])
                    <hr>
                    @include('admin/form/text-input', ['name' => 'i3_solution_point_1', 'label' => 'Selling point'])
                    @include('admin/form/text-input', ['name' => 'i3_solution_point_2', 'label' => 'Selling point'])
                    @include('admin/form/text-input', ['name' => 'i3_solution_point_3', 'label' => 'Selling point'])
                </div>
                <div class="col-sm-6">
                    @include('admin/form/image-form', ['name' => 'i3_featured_image', 'label' => 'Featured Image i3'])
                </div>
            </div>

            {{-- Business Types --}}

            <div class="row pt-4">
                <div class="col-sm-12">
                    <h4>Business Types</h4>
                    <hr>
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'business_title', 'label' => 'Title'])
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'business_sub_title', 'label' => 'Sub Title'])
                </div>
            </div>
            <div class="row pt-4 text-center">
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
            </div>
            <div class="row text-center">
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
                <div class="col d-inline-block">
                    @include('admin/form/icon-form', ['name' => 'bussiness_type_1', 'name_label' => 'bussiness_type_1_label'])
                </div>
            </div>

            {{-- Unix Selling Point --}}

            <div class="row pt-4">
                <div class="col-sm-12">
                    <h4>{{ stringlang('Unique Selling Point', 'Kelebihan Produk') }}</h4>
                    <hr>
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'usp_title', 'label' => 'Title'])
                </div>
            </div>
            <div class="row pt-4">
                <div class="col-sm-6">
                    <div class="row">
                        <div class="col-sm-3">
                            @include('admin/form/image-form', ['name' => 'usp_icon_1', 'label' => 'Icon'])
                        </div>
                        <div class="col-sm-9">
                            @include('admin/form/text-input', ['name' => 'usp_point_1', 'label' => 'Unique selling point text'])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            @include('admin/form/image-form', ['name' => 'usp_icon_2', 'label' => 'Icon'])
                        </div>
                        <div class="col-sm-9">
                            @include('admin/form/text-input', ['name' => 'usp_point_2', 'label' => 'Unique selling point text'])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            @include('admin/form/image-form', ['name' => 'usp_icon_3', 'label' => 'Icon'])
                        </div>
                        <div class="col-sm-9">
                            @include('admin/form/text-input', ['name' => 'usp_point_3', 'label' => 'Unique selling point text'])
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    @include('admin/form/text-input', ['name' => 'usp_youtube_url', 'label' => 'Youtube URL'])
                </div>
            </div>


            <div class="row pt-4">
                <div class="col-sm-12">
                    <h4>Awards</h4>
                    <hr>
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'award_title', 'label' => 'Title'])
                    @include('admin/form/text-input', ['name' => 'award_sub_title', 'label' => 'Sub Title'])
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-sm-12">
                    <h4>Contact Us</h4>
                    <hr>
                </div>
                <div class="col-sm-6 col-sm-offset-3">
                    @include('admin/form/text-input', ['name' => 'cu_pre_title', 'label' => 'Pre Title'])
                    @include('admin/form/text-input', ['name' => 'cu_title', 'label' => 'Title'])
                    @include('admin/form/text-input', ['name' => 'cu_sub_title', 'label' => 'Sub Title'])
                </div>
            </div>



            {{-- <div class="row">
                <div class="col-sm-12 pt-4">
                    <button class="btn btn-primary btn-block">Save Setting</button>
                </div>
            </div> --}}

        </form>
    </div>
</div>

@endsection