<?php $value = editValue($table, $select_id) ?>

<select class="form-control" id="{{$select_id}}" required="" name="{{$select_id}}">
    <option value="{{$default}}">- Select {{$label}} -</option>
</select>

<script>
    $(document).ready(function(){
        
        @if (!empty($value))
        setTimeout(() => {
            $("#{{$parent_select}}").change();
        }, 1000 * {{$timeout}});

        setTimeout(() => {
            $("#{{$select_id}}").val({{$value}});
        }, 1500 * {{$timeout}});
        @endif

        $("#{{$parent_select}}").change(function(){
            var search = $("#{{$parent_select}}").val();
            var url = "{{url('api/option/helper')}}";
            url = url + "?table={{$dataproperties['table']}}";
            url = url + "&select={{urlencode($dataproperties['id'] . ' as select_value, ' . $dataproperties['label'] . ' as select_label')}}";
            url = url + "&label={{$dataproperties['label']}}";
            url = url + "&fk_name={{$dataproperties['search']}}";
            url = url + "&fk_value=" + search;
            url = url + "&datatable_where={{urlencode($dataproperties['where'])}}";

            $.get(url, function(data){
                var option = `<option value="{{$default}}">- Select {{$label}} -</option>`;
                $.each(data, function(key,val){
                    option = option + `<option value="`+val.select_value+`">`+val.select_label+`</option>`;
                    $("#{{$select_id}}").html(option);
                });
            });
        });
    });
</script>