@php 
$id = CRUDBooster::getCurrentId();
$setting = \App\Settings::find($id);
@endphp

@section('codeblock_css')
{{-- <link rel=stylesheet href="{{ asset('codemirror/doc/docs.css') }}"> --}}
<link rel="stylesheet" href="{{ asset('codemirror/lib/codemirror.css') }}">
<link rel="stylesheet" href="{{ asset('codemirror/addon/fold/foldgutter.css') }}">
<link rel="stylesheet" href="{{ asset('codemirror/addon/dialog/dialog.css') }}">
<link rel="stylesheet" href="{{ asset('codemirror/theme/monokai.css') }}">
<style>
.CodeMirror {border-top: 1px solid #eee; border-bottom: 1px solid #eee; line-height: 1.3; height: 500px}
.CodeMirror-linenumbers { padding: 0 8px; }
</style>
@endsection

<article></article>
<textarea name="{{$name}}" id="code-miror-{{$name}}" style="display:none"></textarea>

@section('codeblock_js')
<script src="{{ asset('codemirror/lib/codemirror.js') }}"></script>
<script src="{{ asset('codemirror/addon/search/searchcursor.js') }}"></script>
<script src="{{ asset('codemirror/addon/search/search.js') }}"></script>
<script src="{{ asset('codemirror/addon/dialog/dialog.js') }}"></script>
<script src="{{ asset('codemirror/addon/edit/matchbrackets.js') }}"></script>
<script src="{{ asset('codemirror/addon/edit/closebrackets.js') }}"></script>
<script src="{{ asset('codemirror/addon/comment/comment.js') }}"></script>
<script src="{{ asset('codemirror/addon/wrap/hardwrap.js') }}"></script>
<script src="{{ asset('codemirror/addon/fold/foldcode.js') }}"></script>
<script src="{{ asset('codemirror/addon/fold/brace-fold.js') }}"></script>
<script src="{{ asset('codemirror/mode/javascript/javascript.js') }}"></script>
<script src="{{ asset('codemirror/keymap/sublime.js') }}"></script>
<script>
var value = `{{ !empty($setting->value) ? $setting->value : '' }}`;
var editor = CodeMirror(document.body.getElementsByTagName("article")[0], {
    value: window.atob(value),
    lineNumbers: true,
    mode: "javascript",
    keyMap: "sublime",
    autoCloseBrackets: true,
    matchBrackets: true,
    showCursorWhenSelecting: true,
    theme: "monokai",
    tabSize: 2
});
setInterval(() => {
    $("#code-miror-{{$name}}").val(encodeURI(editor.getValue()));
}, 1000);
</script>
@endsection