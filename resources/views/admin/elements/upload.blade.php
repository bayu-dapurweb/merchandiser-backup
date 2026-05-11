@php $id = md5($name) @endphp

<div class="form-group row" style="padding-top:5px;">
    <label class="control-label col-xs-2" style="padding-top:5px; text-align:right">{{$label}}<span class="text-danger">*</span></label>
    <div class="col-sm-9">        
        <img src="{{url_to_svg($value)}}" id="preview_{{$id}}" style="width:100px; height:100px; object-fit:cover; border:solid 1px silver; border-radius:5px; margin-bottom:10px;">
        <input type="file" class="form-control" id="{{$id}}_upload">
        <input type="text" class="form-control" id="{{$id}}" name="{{$name}}" style="display:none" value={{$value}}>
    </div>
</div>

<script>
$("#{{$id}}_upload").change(function(){
    console.log("initial upload");
    var formData = new FormData();
    formData.append('file', $('#{{$id}}_upload')[0].files[0]);

    $.ajax({
        url : "{{ route('upload-helper') }}",
        type : 'POST',
        data : formData,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        success : function(data) {
            console.log("Upload Res : ", data);
            $("#preview_{{$id}}").attr("src", data.media_blob);
            $("#{{$id}}").val(data.media_url);
            $("#{{$id}}_upload").val("");
        }
    });
});
</script>