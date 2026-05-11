<div class="text-center">
    <label for="{{$name}}">{{$label}}</label>
    <img src="{{ uri('image/default-pic.jpg') }}" class="w-100 d-block img-icon m-0-auto" onclick="$('#{{$name}}').click()">
    <input class="form-control w-100 d-none" name="{{$name}}" type="file" id="{{$name}}">
</div>