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
        SEO Script
    </div>

    <div class="panel-body">

        <div class="pb-3">
            <a href="{{ CRUDBooster::mainpath() }}" class="mb-4"><i class="fa fa-chevron-left"></i> Back to main page ...</a>
        </div>
        

        <form action="{{CRUDBooster::mainpath() . "/seo/" . $id}}" method="POST">
            @csrf
        

            @include("admin/form/seoscript", [
                'name' => $name,
                'value' => $value,
            ])


            <div class="row">
                <div class="col-sm-9 pt-3">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection