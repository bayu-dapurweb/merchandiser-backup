<div class="panel panel-default">
    <div class="panel-heading">
          Instagram Setting
    </div>

    <div class="panel-body">
          <form class="row" method="POST" action="{{CRUDBooster::mainpath()}}/instagramscrap">
                @csrf
              <div class="col-sm-10">
                  <input class="form-control w-100" placeholder="Input instagram account URL" name="link" value="https://www.instagram.com/bakpiatugujogja">
              </div>
              <div class="col-sm-2">
                  <button class="btn btn-primary btn-block">Scrap</button>
              </div>
          </form>
    </div>
</div>