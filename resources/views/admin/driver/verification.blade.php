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
                            <td>{{ dateformat($driver->birthday) }}</td>
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

<div class="panel panel-default">
    <div class="panel-heading">
        Driver Verifications & Bank Information
    </div>

    <div class="panel-body">
        <form method="POST">
            @csrf
            <input type="hidden" name="driver_id" value="{{ $driver->id }}">
            
            @if (env("DRIVER_BANK_DETAIL", false))
            
            @php
            $inputs = [
                "beneficiary_account" 		=> "Nomor Rekening",
                "beneficiary_account_name" 	=> "Atas Nama",
                "beneficiary_va_name" 		=> "Nama Bank",
                "beneficiary_bank_code" 	=> "Kode Bank",
                "beneficiary_bank_branch" 	=> "Cabang Bank",
                "beneficiary_region_code" 	=> "Kode Region",
                // "beneficiary_country_code" 	=> "ID",
                // "beneficiary_purpose_code" 	=> "1",
            ];

            @endphp

            @foreach ($inputs as $key => $label)

            @include("admin/elements/input", [
                'label'         => $label,
                'name'          => $key,
                'placeholder'   => $label,
                'value'         => !empty($old[$key]) ? $old[$key] : $driver->{$key},
                'errors'   => !empty($errors[$key]) ? $errors[$key] : [],
            ])

            @endforeach

            @endif

            <div class="row">
                <div class="col-xs-2"></div>
                <div class="col-xs-8">Saya <b>{{$user_name}}</b>, dengan kewenangan saya sebagai <b>{{ $user_privileg_name }}</b> memutuskan bahwa permohonan pengajuan driver {{ $driver_name }} dengan code {{ $driver_code }} telah : <br><br></div>
                
            </div>

            <div class="row">
                <div class="col-xs-2"></div>
                <div class="col-xs-8">
                    <button class="btn btn-primary" type="submit" name="status" value="approve">Diterima</button>
                    <a class="btn btn-danger" data-toggle="modal" data-target="#reject-modal">Ditolak</a>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="reject-modal" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Alasan Penolakan</h4>
                        </div>
                        <div class="modal-body">
                            <label for="">Alasan Penolakan </label>
                            <textarea name="reject_note" class="form-control" rows="4" placeholder="Tuliskan alasan"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit" name="status" value="reject">Simpan</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Batalkan</button>
                            <!-- Add any additional buttons here if needed -->
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection