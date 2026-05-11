@php 
if (empty($param)) {
    $param = [];
}

$selected_tag = [];
$tagsall = \App\RefTags::where("tag_type", "article")->get();
$id = CRUDBooster::getCurrentId();
$post = \App\TrxPosts::find($id);
if (!empty($post))  {
    $meta = json_decode($post->json_meta);
    if (!empty($meta)) {
        $selected_tag = $meta->tag;
    }
}
@endphp

<select class="form-control select2" name="tag[]" id="tag" multiple>
    <option value="">All</option>
    @foreach ($tagsall as $t)
    <option value="{{slug($t->id)}}" {{ in_array(($t->id), $selected_tag) ? "selected" : ""  }}>{{$t->name}}</option>
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
