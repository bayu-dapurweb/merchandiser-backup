@extends('crudbooster::admin_template')
@section('content')
<style>
    .w-100 {
        max-width: 100%;
    }

    .border-doted {
        border:dotted 2px;
    }
    
    .border-silver {
        border-color: silver;
    }
    
    .bg-white {
        background-color: #fff;
    }
    
    .attachment {
        padding:10px;
        margin-bottom:10px;
    }

    .attachment img {
        margin-bottom: 10px;
        height:100px;
        object-fit: cover;
        width:100%;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Informasi Mitra
                </div>
                <div class="panel-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Mitra Name</td>
                                <td>:</td>
                                <td>{{ $mitra->mitra_name }}</td>
                            </tr>
                            <tr>
                                <td>Store / Shop Name</td>
                                <td>:</td>
                                <td>{{ $mitra->primary_company_name }}</td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td>:</td>
                                <td>{{ $mitra->primary_address }}</td>
                            </tr>
                            <tr>
                                <td>Phone</td>
                                <td>:</td>
                                <td>{{ $mitra->primary_phone }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>:</td>
                                <td>{{ $mitra->primary_email }}</td>
                            </tr>
                            <tr>
                                <td>Company Type</td>
                                <td>:</td>
                                <td>{{ $mitra->company_type }}</td>
                            </tr>
                            <tr>
                                <td>Company Number (TDP)</td>
                                <td>:</td>
                                <td>{{ $mitra->company_reg_number }}</td>
                            </tr>
                            <tr>
                                <td>Region</td>
                                <td>:</td>
                                <td>{{ $region->region_name }}</td>
                            </tr>
                            <tr>
                                <td>Brand</td>
                                <td>:</td>
                                <td>
                                    @php
                                    $mitra_brands = \App\RefMitraBrands::where("ref_mitra_id", $mitra->id)->get()->pluck("ref_brand_id");
                                    $brands = \App\RefBrands::whereIn("id", $mitra_brands)->get();

                                    $html = '';
                                    foreach ($brands as $v) {
                                        $html .= '<a class="btn btn-default btn-xs">'.$v->brand_name.'</a>';
                                    }

                                    @endphp
                                    {!! $html !!}
                                </td>
                            </tr>
                            <tr>
                                <td>Registration Status</td>
                                <td>:</td>
                                <td>
                                    @if ($mitra->registration_status == "approve")
                                        <a class="btn btn-success btn-xs">Approved</a>
                                    @elseif ($mitra->registration_status == "reject")
                                        <a class="btn btn-danger btn-xs">Rejected</a>
                                    @else 
                                        <a class="btn btn-default btn-xs">Waiting for Approval</a>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Bank Information
                </div>
                <div class="panel-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Bank</td>
                                <td>:</td>
                                <td>{{ $bank->bank_name }}</td>
                            </tr>
                            <tr>
                                <td>Bank Code</td>
                                <td>:</td>
                                <td>{{ $bank->bank_code }}</td>
                            </tr>
                            <tr>
                                <td>Branch</td>
                                <td>:</td>
                                <td>{{ $mitra->bank_branch }}</td>
                            </tr>
                            <tr>
                                <td>Reg. Number</td>
                                <td>:</td>
                                <td>{{ $mitra->bank_number }}</td>
                            </tr>
                            <tr>
                                <td>In The Name Of</td>
                                <td>:</td>
                                <td>{{ $mitra->bank_the_name_of }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if (!empty($store))
            @foreach($store as $k => $v)
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Store Information #<?=++$k?>
                        </div>
                        <div class="panel-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td>Store Name</td>
                                        <td>:</td>
                                        <td>{{ $v->store_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Store Type</td>
                                        <td>:</td>
                                        <td>{{ $v->place_type }}</td>
                                    </tr>
                                    <tr>
                                        <td>PIC NAME</td>
                                        <td>:</td>
                                        <td>{{ $v->pic_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>PIC Phone</td>
                                        <td>:</td>
                                        <td>{{ $v->pic_phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>Facebook Username</td>
                                        <td>:</td>
                                        <td>{{ $v->facebook_username }}</td>
                                    </tr>
                                    <tr>
                                        <td>Instagram Username</td>
                                        <td>:</td>
                                        <td>{{ $v->instagram_username }}</td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>:</td>
                                        <td>{{ $v->address }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Attachment
                </div>
            
                <div class="panel-body">
                    <div class="row">
                        @if (!empty($doc)) 
                            @foreach ($doc as $v) 
                                <div class="col-xs-6 col-sm-3">
                                    <div class="attachment border-doted border-silver bg-white">
                                        <img src="{{$v->url}}" class="w-100">
                                        <p class="text-center">{{$v->title}}</p>
                                    </div>
                                    <a href="{{$v->url}}" target="_blank" download class="btn btn-warning btn-block">Download</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Do you want to approve the merchant?
                </div>
                <div class="panel-body text-right">
                    <button name="action" style="width:150px;" data-toggle="modal" data-target="#rejectmodal" class="btn btn-lg btn-danger" value="tolak">Reject</button>
                    <button name="action" style="width:150px;" data-toggle="modal" data-target="#approvemodal" class="btn btn-lg btn-success" value="tolak">Approve</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="rejectmodal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Rejected</h4>
      </div>
      <form class="modal-body" action="{{CRUDBooster::mainpath() . "/merchantinfo/" . $id}}" method="post">
            @csrf
            <label for="reason">Reject Reason</label>
            <textarea class="form-control" placeholder="Reject reason..." name="reason"></textarea>
            <input type="hidden" name="status" value="reject">
            <button type="submit" id="reject-button" style="display:none"></button>
      </form>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="$('#reject-button').click()">Submit</button>
      </div>
    </div>

  </div>
</div>


<!-- Modal -->
<div id="approvemodal" class="modal fade" role="dialog">
    <div class="modal-dialog">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Approved</h4>
        </div>
        <form class="modal-body" action="{{CRUDBooster::mainpath() . "/merchantinfo/" . $id}}" method="post">
                @csrf
              <label for="">Please select Sales</label>
              <select class="form-control" name="sales_id">
                  <option value="">--Select Sales--</option>
                  @if (!empty($sales)) 
                    @foreach ($sales as $v)
                    <option value="{{$v->id}}">{{$v->name}}</option>
                    @endforeach
                  @endif
              </select>
              <input type="hidden" name="status" value="approve">
              <button type="submit" id="approve-button" style="display:none"></button>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="$('#approve-button').click();">Submit</button>
        </div>
      </div>
  
    </div>
  </div>

@endsection