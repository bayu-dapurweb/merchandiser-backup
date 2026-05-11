@php 
$id = CRUDBooster::getCurrentId();
$seo = \App\SeoTag::where([
    ["type", $type],
    ["reff_id", $id],
])->first();
@endphp
<label>{{ stringlang('Title', 'Jabatan') }}</label>
<textarea class="form-control" name="seo_title" rows="2" placeholder="Title">{{old("seo_title", $seo->title)}}</textarea>
<label>Keyword</label>
<textarea class="form-control" name="seo_keyword" rows="2" placeholder="Sparate the keyword with comma">{{old("seo_title", $seo->title)}}</textarea>
<label>Description</label>
<textarea class="form-control" name="seo_description" rows="3" placeholder="Description">{{old("seo_description", $seo->description)}}</textarea>
<label>Custom Script</label>
<textarea class="form-control" name="seo_script" rows="5" placeholder="Your custom javascript">{{old("seo_script", $seo->script)}}</textarea>