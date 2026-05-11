@php 

$price_setting = \App\Settings::where([
    ["key", "PRICE_SPIKE"],
    ["setting_type", "background"]
])->first();

$value = !empty($price_setting) ? $price_setting->value : 100;
@endphp

<div class="panel panel-default">
    <div class="panel-heading">
          Price Spikes
    </div>

    <div class="panel-body">
          <form class="form-inline" method="POST" action="{{CRUDBooster::mainpath()}}/pricespikes" enctype="multipart/form-data">
                @csrf
                <input class="form-control" placeholder="Percentage of rising price (%)" value="{{$value}}" name="kenaikan_harga">
                <button class="btn btn-primary">Submit</button>
                <br>
                <i>100% is normal price, add to to up the price</i>
          </form>
    </div>
</div>