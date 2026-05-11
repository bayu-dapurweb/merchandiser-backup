@extends('crudbooster::admin_template')
@section('content')

<div class="">
    <div class="row">
        <div class="col-sm-7">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Driver Info
                </div>
            
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <th>Driver Code</th>
                            <td>{{ $driver->drivercode }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $driver->name }}</td>
                        </tr>
                        <tr>
                            <th>Birthday</th>
                            <td>{{ dateformatsimple($driver->birthday) }}</td>
                        </tr>
                        <tr>
                            <th>SIM Expired</th>
                            <td>{{ dateformatsimple($driver->sim_expired) }}</td>
                        </tr>
                        <tr>
                            <th>No KTP</th>
                            <td>{{ de($driver->ktp_no) }}</td>
                        </tr>
                        <tr>
                            <th>No SIM</th>
                            <td>{{ de($driver->sim_no) }}</td>
                        </tr>

                        <tr>
                            <th>Gender</th>
                            <td>{{ ucfirst($driver->gender) }}</td>
                        </tr>
                        <tr>
                            <th>Province</th>
                            <td>{{ ucfirst($driver->province->name) }}</td>
                        </tr>
                        <tr>
                            <th>City</th>
                            <td>{{ ucfirst($driver->city->name) }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ ucfirst($driver->address) }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <a href="mailto:{{decrypt_string($driver->email)}}">{{ decrypt_string($driver->email) }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>
                                <p>{{ decrypt_string($driver->phone) }}</p>
                                <a href="https://wa.me/{{ whatsapp_filter(decrypt_string($driver->phone)) }}" class="btn btn-xs btn-success" target="_blank"><i class="fa fa-whatsapp"></i> Whatsapp</a>
                                <a href="tel:+{{ whatsapp_filter(decrypt_string($driver->phone)) }}" class="btn btn-xs btn-primary"><i class="fa fa-phone"></i> Direct Call</a>
                            </td>
                        </tr>
                        
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Driver Document
                </div>
            
                <div class="panel-body">
                    <table class="table">
                        <tr>
                            <th>KTP</th>
                            <td>
                                <a href="{{ $driver->media_ktp->url }}" target="_blank">
                                    <img src="{{ $driver->media_ktp->url }}" alt="" style="max-width:100%; max-height:100px; border-radius:6px">
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>SIM</th>
                            <td>
                                <a href="{{ $driver->media_sim->url }}" target="_blank">
                                    <img src="{{ $driver->media_sim->url }}" alt="" style="max-width:100%; max-height:100px; border-radius:6px">
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>SKCK</th>
                            <td>
                                <a href="{{ $driver->media_skck->url }}" target="_blank">
                                    <img src="{{ $driver->media_skck->url }}" alt="" style="max-width:100%; max-height:100px; border-radius:6px">
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection