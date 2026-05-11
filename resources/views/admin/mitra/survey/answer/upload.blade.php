
<div class="row" style="margin-bottom:4px;">
    <div class="col-sm-12">
        <span>{!! nl2br($answer['answer']) !!}<span>
    </div>
    <div class="col-sm-8">
        <div class="upload-area upload-area-preview-{{$answer['id']}}" 
            onclick="$('.upload-area-{{$answer['id']}}').click();"
            style=""
        ></div>        

        <input type="file" name="answer[{{$answer['id']}}]" style="display:none" class="upload-action upload-area-{{$answer['id']}}" data-id="{{$answer['id']}}">
    </div>
</div>

