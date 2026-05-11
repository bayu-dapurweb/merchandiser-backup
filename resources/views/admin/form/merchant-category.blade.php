<?php $value = editValue($table, $select_id) ?>
<select class="form-control" id="{{$select_id}}" required="" name="{{$select_id}}">
    <option value="0">- Select Category -</option>
    @foreach($options as $k => $v)
        <option value="{{$k}}"  {!! $value == $k ? 'selected="selected"' : '' !!}>{{$v}}</option>
    @endforeach
</select>