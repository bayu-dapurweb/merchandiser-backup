@php


@endphp
<div class="panel panel-default">
    <div class="panel-heading">
          Youtube Video
    </div>

    <div class="panel-body">
          <form class="row" method="POST" action="{{CRUDBooster::mainpath()}}/youtube">
                @csrf
              <div class="col-sm-10">
                  <input class="form-control w-100" placeholder="Input Youtube URL" name="link" value="https://youtu.be/m3o1KpM2g9w">
              </div>
              <div class="col-sm-2">
                  <button class="btn btn-primary btn-block">{{ stringlang('Submit', 'Kirim') }}</button>
              </div>
          </form>
    </div>
</div>