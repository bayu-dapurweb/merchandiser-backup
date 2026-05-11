@php 
if (empty($param)) {
    $param = [];
}
$tagsall = \App\RefTags::get();
// $tagsall = \App\RefTags::where($param)->get();
$id = CRUDBooster::getCurrentId();
$tags = [];
// $tags = \App\PostMeta::where("post_id", $id)
//     ->where("meta_key", "tag")
//     ->get()
//     ->pluck("meta_value")
//     ->toArray();
@endphp

<select class="form-control select2" name="tag[]" id="tag" multiple>
    <option value="">All</option>
    @foreach ($tagsall as $t)
    <option value="{{slug($t->title)}}" {{ in_array(slug($t->title), $tags) ? "selected" : ""  }}>{{$t->title}}</option>
    @endforeach
</select>

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
                $('#tag').select2();
            })
        </script>
@endpush
