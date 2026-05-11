@php 
$id = md5($name); 
$value = json_decode(\App\Settings::find(CRUDBooster::getCurrentId())->value, true);
// dd($value);
@endphp



<table class="table table-bordered">
    <thead>
        <tr>
            @foreach ($header as $h)
            <th>{{ ucfirst($h['label']) }}</th>
            @endforeach
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="{{$id}}_table">
        <tr class="to-be-save">
            @foreach ($header as $k => $h)
            <th>
                @if ($h['type'] == 'input')
                <input type="text" id="{{$id}}_table_{{$k}}"  class="form-control" placeholder="{{$h['label']}}">
                @elseif ($h['type'] == 'textarea')
                <textarea type="text" id="{{$id}}_table_{{$k}}"  class="form-control" placeholder="{{$h['label']}}"></textarea>
                @elseif ($h['type'] == 'select')
                <select type="text" id="{{$id}}_table_{{$k}}"  class="form-control" placeholder="{{$h['label']}}">
                    <option>-Select {{$h['label']}}-</option>
                    @foreach($h['options'] as $op)
                    <option>{{$op}}</option>
                    @endforeach
                </select>
                @elseif ($h['type'] == 'file')
                <img src="{{url('image/default-pic.jpg')}}" id="{{$id}}_table_{{$k}}_preview" style="width:50px; height:50px" onclick="$('#{{$id}}_table_{{$k}}_upload').click()">
                <input type="file" id="{{$id}}_table_{{$k}}_upload" style="display:none">
                <input type="hidden" id="{{$id}}_table_{{$k}}"  class="form-control" value="{{url('image/default-pic.jpg')}}">
                @endif
            </th>
            @endforeach

            <th>
                <a class="btn btn-xs btn-primary" id="{{$id}}_table_add">Add</a>
            </th>
        </tr>
        @if (!empty($value))
            @foreach ($value as $key => $val)
            @php $rowid = $key + 1; @endphp
            <tr class="to-be-save-{{$rowid}}">
                @foreach ($val as $k => $v)
                <th>
                    @if ($header[$k]['type'] == 'input')
                    <input type="text" name="{{$name}}[{{$rowid}}][{{$k}}]" class="form-control" placeholder="" value="{{$v}}" >
                    @elseif ($header[$k]['type'] == 'textarea')
                    <textarea type="text" name="{{$name}}[{{$rowid}}][{{$k}}]" class="form-control" placeholder="" >{{$v}}</textarea>
                    @elseif ($header[$k]['type'] == 'select')
                    <input type="text" name="{{$name}}[{{$rowid}}][{{$k}}]" class="form-control" placeholder="" value="{{$v}}" >
                    @elseif ($header[$k]['type'] == 'file')
                    <img src="{{ !empty($v) ? url_to_svg($v) : url('image/default-pic.jpg')}}" style="width:50px; height:50px" id="preview_{{md5("$name $rowid $k")}}" onclick="$('#file_{{md5("$name $rowid $k")}}').click()">
                    <input type="hidden" name="{{$name}}[{{$rowid}}][{{$k}}]" id="target_{{md5("$name $rowid $k")}}" class="form-control" value="{{ !empty($v) ? $v : url('image/default-pic.jpg')}}">
                    <input type="file" id="file_{{md5("$name $rowid $k")}}" style="display:none">
                    @endif
                </th>
                <script>
                    $("#file_{{md5("$name $rowid $k")}}").change(function(){    
                    var formData = new FormData();
                    formData.append('file', $('#file_{{md5("$name $rowid $k")}}')[0].files[0]);

                    $.ajax({
                        url : "{{ route('upload-helper') }}",
                        type : 'POST',
                        data : formData,
                        processData: false,  // tell jQuery not to process the data
                        contentType: false,  // tell jQuery not to set contentType
                        success : function(data) {
                            console.log("Upload Res : ", data);
                            $("#preview_{{md5("$name $rowid $k")}}").attr("src", data.media_blob);
                            $("#target_{{md5("$name $rowid $k")}}").val(data.media_url);
                        }
                    });
                });
                </script>
                @endforeach
                <th>
                    <a class="btn btn-xs btn-danger" onclick="$('.to-be-save-{{$rowid}}').remove()"><i class="fa fa-trash"></i></a>
                    {{-- <a class="btn btn-xs btn-success" onclick="$('.swapto-{{$rowid - 1}}').remove()"><i class="	fa fa-chevron-circle-up"></i></a>
                    <a class="btn btn-xs btn-primary" onclick="$('.swapto-{{$rowid + 1}}').remove()"><i class="	fa fa-chevron-circle-down"></i></a> --}}
                </th>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
    

<script>
var count_{{$id}} = {{ !empty($rowid) ? $rowid + 2 : obj_biggest($value) }};
$("#{{$id}}_table_add").click(function(){
    id = count_{{$id}} = count_{{$id}} + 1;
    @foreach ($header as $k => $h)
    var val_el_input_{{$id}}_table_{{$k}} = $("#{{$id}}_table_{{$k}}").val();
    $("#{{$id}}_table_{{$k}}").val("");
        @if ($h['type'] == 'file')
        var val_el_input_blob_{{$id}}_table_{{$k}} = $("#{{$id}}_table_{{$k}}_preview").attr("src");
        $("#{{$id}}_table_{{$k}}_preview").attr("src", "{{url('image/default-pic.jpg')}}");
        console.log("TEST:", val_el_input_blob_{{$id}}_table_{{$k}});
        @endif
    @endforeach
    var html = `
        <tr class="to-be-save-`+id+`">
            @foreach ($header as $k => $h)
                <th>
                    @if ($h['type'] == 'input')
                    <input value="`+val_el_input_{{$id}}_table_{{$k}}+`" type="text" name="{{$name}}[`+id+`][{{$k}}]" class="form-control" placeholder="{{$h['label']}}" >
                    @elseif ($h['type'] == 'select')
                    <input value="`+val_el_input_{{$id}}_table_{{$k}}+`" type="text" name="{{$name}}[`+id+`][{{$k}}]" class="form-control" placeholder="{{$h['label']}}" >
                    @elseif ($h['type'] == 'textarea')
                    <textarea type="text" name="{{$name}}[`+id+`][{{$k}}]" class="form-control" placeholder="{{$h['label']}}" >`+val_el_input_{{$id}}_table_{{$k}}+`</textarea>
                    @elseif ($h['type'] == 'file')
                    <img src="`+val_el_input_blob_{{$id}}_table_{{$k}}+`" style="width:50px; height:50px">
                    <input type="hidden" name="{{$name}}[`+id+`][{{$k}}]" class="form-control" value="`+val_el_input_{{$id}}_table_{{$k}}+`">
                    @endif
                </th>
                @endforeach
            <th>
                <a class="btn btn-xs btn-danger" onclick="$('.to-be-save-`+id+`').remove()"><i class="fa fa-trash"></i></a>
            </th>
        </tr>
    `;
    $("#{{$id}}_table").append(html);
});
</script>

@foreach ($header as $k => $h)
@if ($h['type'] == 'file')
<script>
$("#{{$id}}_table_{{$k}}_upload").change(function(){    
    var formData = new FormData();
    formData.append('file', $('#{{$id}}_table_{{$k}}_upload')[0].files[0]);

    $.ajax({
        url : "{{ route('upload-helper') }}",
        type : 'POST',
        data : formData,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        success : function(data) {
            console.log("Upload Res : ", data);
            $("#{{$id}}_table_{{$k}}_preview").attr("src", data.media_blob);
            $("#{{$id}}_table_{{$k}}").val(data.media_url);
        }
    });
});
</script>
@endif
@endforeach