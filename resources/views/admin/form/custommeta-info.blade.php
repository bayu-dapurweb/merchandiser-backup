@php 
if (\Request::segment(3) == "add") {
    $post_id = null;   
} else {
    $post_id = CRUDBooster::getCurrentId();
}

if (!empty($post_id)) {
    $post = \App\Settings::find($post_id);
}


if (empty($setting)) {
    $setting = \App\Settings::where([
        ["key", $post->id],
        ["setting_type", "custom_page_setting"]
    ])->first();
    $settings = json_decode($setting->value, true);
} else {
    $settings = $setting;
}
$json_meta = json_decode($post->value, true);

@endphp

@if (!empty($post_id))
<div class="form-group row" style="padding-top:5px; {{ !empty($editmodule) && $editmodule === "hide" ? 'display:none' : '' }}">
    <label class="control-label col-xs-2" style="padding-top:5px; text-align:right">Module Setting</label>
    <div class="col-sm-10">
        <div class="row">
            <div class="main-form col-sm-12">
                <div class="form-inline">
                    <select class="form-control w-100 col-sm-2" id="module-setting-options">
                        <option value="">-Select Module Option-</option>
                        <option value="input">Text Input</option>
                        <option value="textarea">Textarea</option>
                        <option value="upload">File Upload</option>
                        <option value="table">Table</option>
                        <option value="summernote">Summernote</option>
                    </select>
                    <input class="form-control col-sm-2" id="module-setting-name" placeholder="Name">
                    <input class="form-control col-sm-2" id="module-setting-label" placeholder="Label">
                </div>
            </div>
            <div class="table-setting col-sm-12" style="padding-top:20px; display:none">
                <label>Table Column Setting</label>
                <div class="table-col-list" style="margin-top:10px;">                    
                    <div class="form-inline">
                        <select class="form-control w-100 col-sm-2" id="table-setting-options">
                            <option value="">-Select Module Option-</option>
                            <option value="input">Text Input</option>
                            <option value="textarea">Textarea</option>
                            <option value="file">File Upload</option>
                        </select>
                        <input class="form-control col-sm-2" id="table-setting-name" placeholder="Name">
                        <input class="form-control col-sm-2" id="table-setting-label" placeholder="Label">
                        <input class="form-control col-sm-2" id="table-setting-data" placeholder="Data">
                        <input class="form-control col-sm-2" id="table-setting-query" placeholder="Query">
                        <a class="btn btn-primary text-white add-column"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                
                <div class="next-col-tabel"></div>
            </div>
            <div class="action-form col-sm-12" style="padding-top:10px;">
                <a class="btn btn-primary" id="add-module">Add Module</a>
            </div>
        </div>
    </div>

</div>
<hr>
@endif

@if (!empty($settings))
@foreach($settings as $k => $v)

<div class="py-4" style="border-right:solid 4px silver">
    {{-- <a class="btn btn-danger btn-xs text-white delete-module" data-key={{$k}} style="float:right;margin-right:4px;"><i class="fa fa-trash"></i></a> --}}
    @if ($v['input_type'] != "table" && $v['input_type'] != "upload")
        @include("admin/elements/" . $v['input_type'], [
            'label'         => $v['label'],
            'name'          => "json_meta[".$v['name']."]",
            'placeholder'   => $v['label'],
            'value'         => old("json_meta.".$v['name'], $json_meta[$v['name']]),
        ])
    @elseif ($v['input_type'] == "upload")
        @include("admin/elements/" . $v['input_type'], [
            'label'         => $v['label'],
            'name'          => "json_meta[".$v['name']."]",
            'placeholder'   => $v['label'],
            'value'         => old("json_meta." . $v['name'], !empty($json_meta[$v['name']]) ? $json_meta[$v['name']] : url('image/default-pic.jpg')),  
        ])
    @else
        @include("admin/elements/" . $v['input_type'], [
            'label'         => $v['label'],
            'name'          => "json_meta[".$v['name']."]",
            'placeholder'   => $v['label'],
            'value'         => old("json_meta.".$v['name'], $json_meta[$v['name']]),
            'header'        => (json_decode($v['data'], true)['header']) ? (json_decode($v['data'], true)['header']) : [],
        ])
    @endif
</div>
@endforeach
@endif

<script>
    $(".form-group-json_meta.control-label").hide();    

    $("#module-setting-options").change(function(){
        var option = $(this).val();
        if (option == "table") {
            $(".table-setting").show();
        } else {
            $(".table-setting").hide();
        }
    });

    $("#add-module").click(function(){
        if ($("#module-setting-options").val() == "") {
            alert("Option type is mandatory");
        } else if ($("#module-setting-name").val() == "") {
            alert("Module name is mandatory");
        } else if ($("#module-setting-label").val() == "") {
            alert("Module label is mandatory");
        } else {
            
            $.post("{{ route('post.pagesetting.helper') }}", {
                "post_id" : '{{$post_id}}',
                "input_type": $("#module-setting-options").val(),
                "name": $("#module-setting-name").val(),
                "label": $("#module-setting-label").val(),
                "table_setting": $("#module-setting-options").val() == 'table' ? TABLE_SETTING : null
            }, function(){
                location.reload();
            })
        }
    
    });

    $(".delete-module").click(function(){
        $.post("{{ route('post.pagesetting.delete.helper') }}", {
            "post_id" : '{{$post_id}}',
            "key": $(this).attr("data-key"),
        }, function(){
            location.reload();
        })
    })

    var TABLE_SETTING = [];
    $(".add-column").click(function(){
        console.log('column add log');
        
        var options = $("#table-setting-options").val();
        var name = $("#table-setting-name").val();
        var label = $("#table-setting-label").val();
        var data = $("#table-setting-data").val();
        var query = $("#table-setting-query").val();

        TABLE_SETTING.push({
            "options" : options,
            "name" : name,
            "label" : label,
            "data" : data,
            "query" : query,
        })

        console.log('TABLE_SETTING add log', TABLE_SETTING);

        setTimeout(() => {
            rendertable()
        },200)

        $("#table-setting-options").val("");
        $("#table-setting-name").val("");
        $("#table-setting-label").val("");
        $("#table-setting-data").val("");
        $("#table-setting-query").val("");
    });

    function rendertable() {
        $(".next-col-tabel").html("");
        $.each(TABLE_SETTING, function(k,v){
            html = `
            <div class="table-col-list" style="margin-top:10px;">
                <div class="form-inline">
                    <input value="`+v.options+`" class="form-control col-sm-2" placeholder="Options" disabled>
                    <input value="`+v.name+`" class="form-control col-sm-2" placeholder="Name" disabled>
                    <input value="`+v.label+`" class="form-control col-sm-2" placeholder="Label" disabled>
                    <input value="`+v.data+`" class="form-control col-sm-2" placeholder="Data" disabled>
                    <input value="`+v.query+`" class="form-control col-sm-2" placeholder="Query" disabled>
                    <a class="btn btn-danger text-white"><i class="fa fa-trash"></i></a>
                </div>
            </div>
            `;
            $(".next-col-tabel").append(html);
        })
    }

</script>