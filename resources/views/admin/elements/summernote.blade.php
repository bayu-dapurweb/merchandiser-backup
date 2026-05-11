@php $id = md5($name) @endphp

<div class="form-group row" style="padding-top:5px;">
    <label class="control-label col-xs-2" style="padding-top:5px; text-align:right">{{$label}}<span class="text-danger">*</span></label>
    <div class="col-sm-9">
        <textarea class="form-control w-100" id="summernote_{{$id}}" placeholder="{{$placeholder}}" name="{{$name}}">{{$value}}</textarea>
    </div>
</div>

<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
$(document).ready(function() {
    $('#summernote_{{$id}}').summernote({
        height: 500,
    });
});
</script>