@php 
$settings = json_decode($setting->value, true);
$json_meta = json_decode($post->json_meta, true);
@endphp
@if (!empty($settings))
@foreach($settings as $v)
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
@endforeach
@endif

<script>
    $(".form-group-json_meta.control-label").hide();
</script>