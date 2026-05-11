<?php
$value = editValue($table, $select_id);
if (!empty($value)) {
    $media = \App\Medias::find($value);
    if (!empty($media)) {
        $url = $media->url;
    } else {
        $url = url('image/default-pic.jpg');    
    }
} else {
    $url = url('image/default-pic.jpg');
}
?>

<div class="">
    <img src="{{$url}}" id="preview_{{$select_id}}" style="width:100px; height:100px; object-fit:cover; border:solid 1px silver; border-radius:5px; margin-bottom:10px;">
    <input type="file" class="form-control" name="{{$select_id}}_upload" id="{{$select_id}}_upload">
    <input type="text" class="form-control" name="{{$select_id}}" id="{{$select_id}}" style="display:none" value={{$value}}>
</div>

<script>
$("#{{$select_id}}_upload").change(function(){
    var formData = new FormData();
    formData.append('file', $('#{{$select_id}}_upload')[0].files[0]);

    $.ajax({
        url : "{{ route('upload-helper') }}",
        type : 'POST',
        data : formData,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        success : function(data) {
            console.log("Upload Res : ", data);
            $("#preview_{{$select_id}}").attr("src", data.media_url);
            $("#{{$select_id}}").val(data.media_id);
        }
    });
});
</script>