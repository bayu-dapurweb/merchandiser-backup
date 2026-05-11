@extends('crudbooster::admin_template')
@section('content')

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-sm-3">
                <h4>Excel Import</h4>
            </div>
        </div>        
    </div>
    
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form action="{{ CRUDBooster::mainpath() . "/import" }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="alert alert-info">
                        <ul>
                            <li>File Harus berektensi XLSX (> excel 2007)</li>
                            <li>Harus menggunakan format file export</li>
                            <li>Hindari penggunaan special character</li>
                            <li>Pastikan field sudah terisi sempurna</li>
                            <li>Hindari multiple value dalam 1 column</li>
                            <li>Hanya akan memproses data yang memiliki nomor</li>
                            <li>Download XLS Sample <a href="{{ uri( !empty($sample) ? $sample : 'sample/salutaria-user-import-sample.xlsx' ) }}">disini</a></li>
                        </ul>
                    </div>

                    <div class="col-sm-6">
                        <input type="file" name="upload" class="form-control">
                    </div>

                    <div  class="col-sm-6">
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection