<div class="text-center">
    <label for="{{$name}}">Icon</label>
    <img src="{{ uri('image/default-pic.jpg') }}" class="w-50 d-block img-icon m-0-auto" onclick="$('#{{$name}}').click()">
    <input class="form-control w-100 d-none" name="{{$name}}" type="file" id="{{$name}}">

    <label for="{{$name_label}}">Label</label>
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Icon label">
        <span class="input-group-btn">
            <button class="btn btn-primary" type="button">
            <i class="fa fa-save"></i>
            </button>
        </span>
    </div>
</div>