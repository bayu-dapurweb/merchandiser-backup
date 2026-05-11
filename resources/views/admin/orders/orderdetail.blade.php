@extends('crudbooster::admin_template')
@section('content')
<style>
    .pt-4 {
        padding-top:40px;
    }

    .d-block {
        display: block;
    }

    .d-none {
        display: none !important;
    }

    .img-icon {
        widtd:60px;
        height:60px;
    }

    .text-center {
        text-align: center;
    }

    .m-0-auto {
        margin: 0 auto !important;
    }

    .no-float: {
        float:none !important;
    }

    .d-inline-block {
        display: inline-block;
    }

    .col {
        widtd:200px
    }

    .mb-3 {
        margin-bottom: 30px;
    }

    .nav .active a {
        background-color: rgb(121, 121, 121) !important;
        border:solid 1px silver;
        color:#fff !important;
    }
</style>

<div class="panel panel-default">
    <div class="panel-heading">
        Order Detail
    </div>

    <div class="panel-body">
        
        <table class="table table-stripes">
            <tbody>
                <tr>
                    <td>Order code</td>
                    <td>{{ $order->ordercode }}</td>
                </tr>
                <tr>
                    <td>Order At</td>
                    @php
                    $transaction_date = "";
                    if (!empty($order->start_trip_at)) {
                        $transaction_date = $order->start_trip_at;
                    } else if (!empty($order->flip_paid_at)) {
                        $transaction_date = $order->flip_paid_at;
                    } else {
                        $transaction_date = $order->updated_at;
                    }
                    
                    @endphp
                    <td>{{ dateformat($transaction_date) }}</td>
                </tr>
                <tr>
                    <td>Status Transaksi</td>
                    <td>
                        @php 
                        $color = [
                            'paid' => 'success',
                            'draft' => 'default',
                            'waiting for payment' => 'warning',
                            'cancle' => 'danger',
                            'expired' => 'danger',
                            'selecting_car' => 'info',
                        ];
                
                        echo '<a class="btn btn-xs btn-'.$color[$order->transaction_status].'">'.ucfirst($order->transaction_status).'</a>';
                        @endphp
                    </td>
                </tr>
                <tr>
                    <td>Order type</td>
                    <td>{{ ucfirst($order->order_type) }}</td>
                </tr>
                <tr>
                    <td>Trip type</td>
                    <td>{{ ucfirst(str_replace("_", " ", $order->trip_type)) }}</td>
                </tr>
                @if ($order->order_type != "rental")
                <tr>
                    <td>Distance</td>
                    <td>{{ nominal($order->distance) }} Km</td>
                </tr>
                @else
                <tr>
                    <td>With Driver</td>
                    <td>{{ $order->is_with_driver ? "Yes" : "No" }}</td>
                </tr>
                @endif
                <tr>
                    <td>Driver</td>
                    <td>{{ ucfirst($order->driver->name) }}</td>
                </tr>
                <tr>
                    <td>Driver Phone</td>
                    <td>
                        @if (!empty($order->driver->user->phone))
                        <a target="_blank" href="https://wa.me/{{ whatsapp_filter(decrypt_string($order->driver->user->phone)) }}" class="btn btn-xs btn-success"><i class="fa fa-whatsapp"></i>&nbsp;{{ decrypt_string($order->driver->user->phone) }}</a>
                        <a target="_blank" href="tel:{{ (decrypt_string($order->driver->user->phone)) }}" class="btn btn-xs btn-primary"><i class="fa fa-phone"></i>&nbsp;{{ decrypt_string($order->driver->user->phone) }}</a>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Car Type</td>
                    <td>{{ ucfirst($order->cartype->typename) }}</td>
                </tr>
                <tr>
                    <td>Plate Number</td>
                    <td>{{ ucfirst($order->car->police_number) }}</td>
                </tr>
                <tr>
                    <td>Customer Name</td>
                    <td>{{ ucfirst($order->user->fullname) }}</td>
                </tr>
                <tr>
                    <td>Customer Email</td>
                    <td>{{ (de($order->user->gmail)) }}</td>
                </tr>
                <tr>
                    <td>Customer Phone</td>
                    <td>
                        
                        @if (!empty(decrypt_string($order->user->phone)))
                        <a target="_blank" href="https://wa.me/{{ whatsapp_filter(decrypt_string($order->user->phone)) }}" class="btn btn-xs btn-success"><i class="fa fa-whatsapp"></i>&nbsp;{{ decrypt_string($order->user->phone) }}</a>
                        <a target="_blank" href="tel:{{ (decrypt_string($order->user->phone)) }}" class="btn btn-xs btn-primary"><i class="fa fa-phone"></i>&nbsp;{{ decrypt_string($order->user->phone) }}</a>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="">
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Pickup Location
                </div>
            
                <div class="panel-body">
                    
                    <table class="table table-stripes">
                        <tbody>
                            <tr>
                                <td>Location Name</td>
                                <td>{{ ucfirst($pickup->label) }}</td>
                            </tr>
                            <tr>
                                <td style="width:150px;">Address</td>
                                <td>{{ ucfirst($pickup->address) }}</td>
                            </tr>
                            <tr>
                                <td>Note</td>
                                <td>{{ ucfirst($pickup->note) }}</td>
                            </tr>
                            <tr>
                                <td>Picked Up at</td>
                                <td>{{ dateformat($order->pickup_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Destination / Return Point
                </div>
            
                <div class="panel-body">
                    
                    <table class="table table-stripes">
                        <tbody>
                            <tr>
                                <td>Location Name</td>
                                <td>{{ ucfirst($dropoff->label) }}</td>
                            </tr>
                            <tr>
                                <td style="width:150px;">Address</td>
                                <td>{{ ucfirst($dropoff->address) }}</td>
                            </tr>
                            <tr>
                                <td>Note</td>
                                <td>{{ ucfirst($dropoff->note) }}</td>
                            </tr>
                            <tr>
                                <td>Arrived / Return at</td>
                                <td>{{ dateformat($order->return_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @php 
    $grand_total = $order->grand_total;
    $additional_total = 0;
    @endphp
    @if (!empty($additional))
    @php 
    $grand_total += $additional->toll;
    $grand_total += $additional->parking;
    $grand_total += $additional->others;
    $grand_total += $additional->tips;

    $additional_total += $additional->toll;
    $additional_total += $additional->parking;
    $additional_total += $additional->others;
    $additional_total += $additional->tips;
    @endphp
    <div class="col-xs-12 col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Additional Charge
            </div>
        
            <div class="panel-body">
                
                <table class="table table-stripes">
                    <tbody>
                        <tr>
                            <td>Toll</td>
                            <td>{{ nominal($additional->toll) }}</td>
                        </tr>
                        <tr>
                            <td>Parking</td>
                            <td>{{ nominal($additional->parking) }}</td>
                        </tr>
                        <tr>
                            <td>Others</td>
                            <td>{{ nominal($additional->others) }}</td>
                        </tr>
                        <tr>
                            <td>Tips</td>
                            <td>{{ nominal($additional->tips) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="col-xs-12 col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Gross Price Detail
            </div>
        
            <div class="panel-body">
                
                <table class="table table-stripes">
                    <tbody>
                        <tr>
                            <td>Basic Price</td>
                            <td>{{ nominal($order->basic_price) }}</td>
                        </tr>
                        <tr>
                            <td>Platform Fee</td>
                            <td>{{ nominal($order->platform_fee) }}</td>
                        </tr>
                        <tr>
                            <td>Discount Amount</td>
                            <td>{{ nominal($order->discount_amount) }}</td>
                        </tr>
                        @if (!empty($additional_total))
                        <tr>
                            <td>Additional Charge</td>
                            <td>{{ nominal($additional_total) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Grand Total</td>
                            <td>{{ nominal($grand_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- <div class="col-xs-12 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                Netto Price Detail
            </div>
        
            <div class="panel-body">
                <table class="table table-stripes">
                    <tbody>
                        <tr>
                            <td>Fee Nominal for Driver</td>
                            <td>{{ nominal($order->driver_fee_value) }}</td>
                        </tr>
                        <tr>
                            <td>Fee Percent for Driver</td>
                            <td>{{ nominal($order->driver_fee_percent) }}%</td>
                        </tr>
                        <tr>
                            <td>Fee Nominal for Evista</td>
                            <td>{{ nominal(($order->grand_total - $order->driver_fee_value)) }}</td>
                        </tr>
                    </tbody>
                </table>
                <i>Fee will be shown after the order completed</i>
            </div>
        </div>
    </div> --}}
    @if (env("OVERIDE_PAYMENT"))
    <div class="col-xs-12 col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Action
            </div>
        
            <div class="panel-body">
                <form action="" method="post">
                    @csrf
                    <button class="btn btn-primary" name="set_as_complete" value="1"><i class="fa fa-check"></i> Set As Complete</button>
                    <button class="btn btn-success" name="set_as_paid" value="1"><i class="fa fa-check"></i> Set As Paid</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection