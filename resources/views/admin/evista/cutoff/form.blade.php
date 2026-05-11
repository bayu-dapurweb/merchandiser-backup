@extends('crudbooster::admin_template')
@section('content')

<div class="">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Cutoff Form
                </div>
            
                <div class="panel-body">
                    <form action="" method="post">
                        @csrf
                        <div class="row">
                            <label for="" class="col-sm-12">Cutoff Date</label>
                            <div class="col-sm-3">
                                <input type="date" name="tgl_cutoff" class="form-control" placeholder="Cutoff Date" required>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-12">
                                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Submit</button>
                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <br>
                                <div class="alert alert-info">
                                    <ul>
                                        <li>Only calculate on selected date</li>
                                        <li>Only calculate driver's paired order</li>
                                        <li>This proccess can not be rolled back</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>


@endsection