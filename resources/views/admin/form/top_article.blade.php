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

<div class="row">
    <div class="col-sm-12 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                  Top Content - ID
            </div>
        
            <div class="panel-body">
                <form class="" method="POST" action="{{CRUDBooster::mainpath()}}/toparticleid">
                    @csrf
                    @include('admin/form/select2', [
                        'name' => 'main_top_article_id',
                        'label' => 'Main Top Article',
                        'option' => $data,
                        'selected' => $top_article_id->main_top_article_id
                    ])
                    @include('admin/form/select2', [
                        'name' => 'sec_1_top_article_id',
                        'label' => 'Secondary Top Article 1',
                        'option' => $data,
                        'selected' => $top_article_id->sec_1_top_article_id
                    ])
                    @include('admin/form/select2', [
                        'name' => 'sec_2_top_article_id',
                        'label' => 'Secondary Top Article 2',
                        'option' => $data,
                        'selected' => $top_article_id->sec_2_top_article_id
                    ])

                    <button type="submit" class="btn btn-primary">{{ stringlang('Submit', 'Kirim') }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                  Top Content - EN
            </div>
        
            <div class="panel-body">
                <form class="" method="POST" action="{{CRUDBooster::mainpath()}}/toparticleen">
                    @csrf
                    @include('admin/form/select2', [
                        'name' => 'main_top_article_en',
                        'label' => 'Main Top Article',
                        'option' => $data,
                        'selected' => $top_article_en->main_top_article_en
                    ])
                    @include('admin/form/select2', [
                        'name' => 'sec_1_top_article_en',
                        'label' => 'Secondary Top Article 1',
                        'option' => $data,
                        'selected' => $top_article_en->sec_1_top_article_en
                    ])
                    @include('admin/form/select2', [
                        'name' => 'sec_2_top_article_en',
                        'label' => 'Secondary Top Article 2',
                        'option' => $data,
                        'selected' => $top_article_en->sec_2_top_article_en
                    ])

                    <button type="submit" class="btn btn-primary">{{ stringlang('Submit', 'Kirim') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection


@push('head')
    <link rel='stylesheet' href='<?php echo asset("vendor/crudbooster/assets/select2/dist/css/select2.min.css")?>'/>
    <style type="text/css">
        .select2-container--default .select2-selection--single {
            border-radius: 0px !important
        }

        .select2-container .select2-selection--single {
            height: 35px
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3c8dbc !important;
            border-color: #367fa9 !important;
            color: #fff !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
        }
    </style>
@endpush
@push('bottom')
<script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
<script type="text/javascript">
    $(function () {
        $('.select2').select2();
    })
</script>
@endpush