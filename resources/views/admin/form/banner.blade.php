<div class="panel panel-default">
    <div class="panel-heading">
          Banner Setting
    </div>

    <div class="panel-body">
          <form class="row" method="POST" action="{{CRUDBooster::mainpath()}}/productbanner" enctype="multipart/form-data">
                @csrf
              <div class="col-sm-10">
                  <label for="">Product Banner</label>
                  <input class="form-control w-100" name="banner" type="file">
              </div>
              <div class="col-sm-2">
                    <label for="">&nbsp;</label>
                    <button class="btn btn-primary btn-block">Upload</button>
              </div>
          </form>
    </div>
</div>