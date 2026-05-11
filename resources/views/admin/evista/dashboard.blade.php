@extends('crudbooster::admin_template')
@section('content')

<style>
    .total-card {
        height: 100px;
        width: 100%;
        background-size: cover;
        background-position: top right;
        background-repeat: no-repeat;
        border-radius: 18px;
        padding-top: 18px;
        padding-left: 26px;
    }

    .total-card small {
        color: #fff;
        font-size: 16px;
        display: block;
        font-weight: bold;
    }

    .total-card p {
        color: #fff;
        font-size: 25px;
        display: block;
        font-weight: bold;
    }

    .report-container-body {
        border-radius: 18px;
        background-color: #fff;
        min-height: 100px;
        width: 100%;
        margin-top:30px;
        padding:25px;
    }

    .report-container-body h4 {
        color: #2B3674;
        margin-top: 0px;
        font-weight: bold;
    }

    #revenueChart {
        width: 100%;
        height: 300px;
    }

    #pieChart {
        width: 300px;
        height: 300px;
    }

    .rating-container {
        display: inline-block;
        margin-left: 10px;
    }
    .rating-container i {
        
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // TOTAL OMSET HARIAN
    var omsetharian = {
      labels: {!! json_encode($omset_datelist) !!},
      datasets: [
        {
          label: "Trip Omset",
          backgroundColor: "rgba(77, 29, 254, 1)",
          borderColor: "rgba(77, 29, 254, 1)",
          borderWidth: 1,
          data: {{ json_encode($omset_dateval) }},
        },
      ],
    };

    // TOTAL REVENUE
    var data = {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      datasets: [
        {
          label: "Jemput Sekarang",
          backgroundColor: "rgba(77, 29, 254, 1)",
          borderColor: "rgba(77, 29, 254, 1)",
          borderWidth: 1,
          data: {{ json_encode($total_revenue_by_month['direct']) }},
        },
        {
          label: "Jemput Nanti",
          backgroundColor: "rgba(44, 203, 180, 1)",
          borderColor: "rgba(44, 203, 180, 1)",
          borderWidth: 1,
          data: {{ json_encode($total_revenue_by_month['later']) }},
        },
        {
          label: "Car Rental",
          backgroundColor: "rgba(255, 213, 106, 1)",
          borderColor: "rgba(255, 213, 106, 1)",
          borderWidth: 1,
          data: {{ json_encode($total_revenue_by_month['rental']) }},
        },
      ],
    };

    // PIE CHART
    var piechart = {
            labels: ["Jemput Sekarang", "Jemput Nanti", "Car Rental"],
            datasets: [{
                data: [{{ $total_revenue_by_trip['direct'] }}, {{ $total_revenue_by_trip['later'] }}, {{ $total_revenue_by_trip['rental'] }}], // Percentages should add up to 100
                backgroundColor: ["rgba(77, 29, 254, 1)", "rgba(44, 203, 180, 1)", "rgba(255, 213, 106, 1)"],
                borderColor: ["rgba(77, 29, 254, 1)", "rgba(44, 203, 180, 1)", "rgba(255, 213, 106, 1)"],
                borderWidth: 1
            }]
        };
  
    // Create a bar chart
    document.addEventListener("DOMContentLoaded", function () {
        // Create a bar chart
        var ctx = document.getElementById("revenueChart").getContext("2d");
        var myChart = new Chart(ctx, {
            type: "bar",
            data: data,
            options: {
                scales: {
                    x: {
                        beginAtZero: true,
                    },
                    y: {
                        beginAtZero: true,
                    },
                },
                legend: {
                    display: false,
                },
            },
            
        });
        var ctx2 = document.getElementById("pieChart").getContext("2d");
        var myChart2 = new Chart(ctx2, {
            type: "pie",
            data: piechart,
            options : {
                legend: {
                    display: false,
                },
            }
        });
        var ctx3 = document.getElementById("omsetharian").getContext("2d");
        
        var myChart3 = new Chart(ctx3, {
            type: "line",
            data: omsetharian,
            options: {
                // scales: {
                //     x: {
                //         beginAtZero: true,
                //     },
                //     y: {
                //         beginAtZero: true,
                //     },
                // },
                legend: {
                    display: false,
                },
                maintainAspectRatio: false,
                responsive: true,
            },
        });
    });
  </script>
  

<div class="">
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <form action="" class="form-inline" method="get" style="text-align: right"> 
                <select name="cartypes" class="form-control">
                    <option value="">Select Car</option>
                    @foreach ($cartypes as $v)
                        @if (get('cartypes') == $v->id) 
                        <option value="{{$v->id}}" selected>{{ $v->typename }}</option>
                        @else
                        <option value="{{$v->id}}">{{ $v->typename }}</option>
                        @endif
                    @endforeach
                </select>
                <input type="date" class="form-control" name="datestart" value="{{ get("datestart", $datestart) }}">
                <input type="date" class="form-control" name="dateend" value="{{ get("dateend", $dateend) }}">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
            </form>
        </div>
    </div>
    <div class="row" style="margin-top: 30px">        
        <div class="col-xs-12 col-md-4">
            <div class="total-card" style="background-image: url('{{ uri('dashboard/dashboard-total-1.png') }}')">
                <small>Total Omeset</small>
                <p>Rp{{ nominal($total_omset) }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="total-card" style="background-image: url('{{ uri('dashboard/dashboard-total-2.png') }}')">
                <small>Total Order</small>
                <p>{{ nominal($total_order) }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="total-card" style="background-image: url('{{ uri('dashboard/dashboard-total-3.png') }}')">
                <small>Total User Register</small>
                <p>{{ nominal($total_user_register) }}</p>
            </div>
        </div>

        <div class="col-xs-12 col-md-8">
            <div class="report-container-body">
                <h4>Total Ritase</h4>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="col-xs-12 col-md-4">
            <div class="report-container-body">
                <h4>Total Ritase</h4>
                <canvas id="pieChart" width="300" height="300"></canvas>
            </div>
        </div>

        <div class="col-xs-12 col-md-12">
            <div class="report-container-body" style="height: 500px;">
                <h4>Omset Harian</h4>
                <canvas id="omsetharian" width="100%" height="400" style="height: 400px;"></canvas>
            </div>
        </div>


        
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="report-container-body">
                <h4>Top 10 Driver Report</h4>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Driver</th>
                                <th>Jumlah Ritase</th>
                                <th>Komisi Driver</th>
                                <th>Omset Driver</th>
                                <th>Rating Driver</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driver as $k => $v)
                            <tr>
                                <td>{{ ++$k }}</td>
                                <td>{{ $v->name . " (".$v->code.")" }}</td>
                                <td>{{ nominal($v->jumlah_ritase) }}</td>
                                <td>{{ nominal($v->total_komisi) }}</td>
                                <td>{{ nominal($v->omset_driver) }}</td>
                                <td>
                                    <div class="rating-container">
                                        @for ($i = 1; $i <= ceil($v->rating_avg); $i++)
                                        <img src="{{uri('dashboard/active-star.png')}}" alt="inactive star">
                                        @endfor
                                        @for ($i = 1; $i <= ( 5 - ceil($v->rating_avg)); $i++)
                                        <img src="{{uri('dashboard/inactive-star.png')}}" alt="inactive star">
                                        @endfor
                                    </div>
                                    <div class="rating-container">
                                        <span class="text-black">{{ round($v->rating_avg,1) }}</span>/<span class="text-secondary">5</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection