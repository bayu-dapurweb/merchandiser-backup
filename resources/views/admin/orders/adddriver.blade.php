@extends('crudbooster::admin_template')
@section('content')

<div class="panel panel-default">
    <div class="panel-heading">
        Available Driver
    </div>

    <div class="panel-body">
        
        <form action="" class="form-inline" style="margin-bottom:40px;">
            <input type="text" class="form-control" name="search" value="{{get('search')}}" placeholder="Search driver name...">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search Driver</button>
        </form>
        

        <table class="table">
            <thead>
                <tr>
                    <th>Driver Code</th>
                    <th>Driver Name</th>
                    <th>Active Car</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($drivers as $v)
                <tr>
                    <td>{{ $v->drivercode }}</td>
                    <td>{{ $v->name }}</td>
                    <td>{{ $v->car->police_number ? $v->car->police_number : "no selected car" }}</td>
                    <td>
                        <form action="" method="POST">
                            @csrf
                            <input type="hidden" value="{{ $v->id }}" name="driver_id">
                            <input type="hidden" value="{{ $order->id }}" name="order_id">
                            <button type="submit" name="selected" value="1" class="btn btn-primary btn-xs"><i class="fa fa-car"></i> Select Driver</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection