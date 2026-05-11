@extends('crudbooster::admin_template')
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Driver Information
            </div>
        
            <div class="panel-body">
                
                <table class="table table-stripe">
                    <tr>
                        <th>Nama Driver</th>
                        <td>{{ $driver->name }}</td>
                    </tr>
                    <tr>
                        <th>Driver Code</th>
                        <td>{{ $driver->drivercode }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>
                            @if (!empty($driver->user->phone))
                            <a target="_blank" href="https://wa.me/{{ whatsapp_filter(decrypt_string($driver->user->phone)) }}" class="btn btn-xs btn-success"><i class="fa fa-whatsapp"></i>&nbsp;{{ decrypt_string($driver->user->phone) }}</a>
                            @else 
                            <i>non phone found</i>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Nominal</th>
                        <td>Rp{{  nominal($withdraw->nominal) }} </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-sm-6" style="display: none">
        <div class="panel panel-default">
            <div class="panel-heading">
                Withdraw Information
            </div>
        
            <div class="panel-body">
                <table class="table table-stripe">
                    <tr>
                        <th>Bank</th>
                        <td>BCA</td>
                    </tr>
                    <tr>
                        <th>Rekening</th>
                        <td>671287832823829</td>
                    </tr>
                    <tr>
                        <th>Atas Nama</th>
                        <td>671287832823829</td>
                    </tr>
                    <tr>
                        <th>Nominal</th>
                        <td>{{  nominal(1823786) }} </td>
                    </tr>
                </table>
                
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        Approval
    </div>

    <div class="panel-body">
        @if ($error)
        <div class="alert alert-danger">
            <p>Error, you must do this and that</p>
        </div>
        @endif
        <form action="" method="post">
            @csrf
            <p>Saya <b>{{$user_name }}</b> selaku <b>{{$user_privileg_name}}</b> memberikan keputusan bahwa permintaan penarikan penghasilan ini 
                <select name="status" id="status">
                    <option value="paid">diterima</option>
                    <option value="reject">ditolak</option>
                </select>

                <span class="reason" style="display: none">dengan alasan : </span><br>
                <textarea name="note" row="40" cols="40" id="note" style="display: none"></textarea>
            </p>

            <button type="submit" class="btn btn-md btn-primary"><i class="fa fa-save"></i> Submit</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    $("#status").change(function(){
        var val = $(this).val();
        if (val == "reject") {
            $("#note").show();
            $(".reason").show();
        } else {
            $("#note").hide();
            $(".reason").hide();
        }
    })
})
</script>

@endsection