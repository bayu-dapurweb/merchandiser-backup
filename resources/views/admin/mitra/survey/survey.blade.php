@extends('crudbooster::admin_template')
@section('content')
<style>
    .survey-menu img {
        height: 50px;
    }

    .survey-menu p {
        color: #111111;
        font-weight: bold;
        padding-top:7px;
        padding-bottom:7px;
    }

    .survey-menu a:hover {
        cursor: pointer;
    }

    .survey-menu a:hover > p{
        cursor: pointer;
        color: #0B6955;
        border-bottom : solid 5px #0B6955;
    }

    .survey-menu .active p {
        color: #0B6955;
        font-weight: bold;
        padding-top:7px;
        border-bottom : solid 5px #0B6955;
        padding-bottom:7px;
    }

    .upload-area {
        width:100%;
        height: 200px;
        border:dotted 2px silver;
        border-radius: 15px;
        background-image: url('{{ asset('assets/img/upload-icon.png') }}');
        background-position:center;
        background-repeat:no-repeat;
    }
    .upload-area:hover {
        cursor: pointer;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Survey
                </div>
                <div class="panel-body">
                    <h3 class="text-success"><b>{{ $mitra->primary_company_name }}</b></h3>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Regional</td>
                                <td style="width:30px;text-algin:center;">:</td>
                                <td>{{ $region->region_name }}</td>
                                <td>Brand</td>
                                <td style="width:30px;text-algin:center;">:</td>
                                <td>
                                    @foreach ($brand as $v)
                                    <a class="btn btn-default btn-xs">{{$v->brand_name}}</a>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td>Pemilik</td>
                                <td>:</td>
                                <td>{{ $mitra->mitra_name }}</td>
                                <td>Alamat</td>
                                <td>:</td>
                                <td>{{ $mitra->primary_address }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                    <div class="container-fluid">
                        <div class="row survey-menu">
                            <a class="col-sm-2 text-center {{ get('cat', 1) == 1 ? 'active' : '' }}" href="?cat=1">
                                <img src="{{ asset('assets/img/target-'.(get('cat', 1) == 1 ? 'green' : 'black').'-icon.png') }}" data-inactive="{{ asset('assets/img/target-'.(get('cat', 1) == 1 ? 'green' : 'black').'-icon.png') }}" data-active="{{ asset('assets/img/target-green-icon.png') }}">
                                <div class="menu-icon"></div>
                                <p>Target Market</p>
                            </a>
                            <a class="col-sm-2 text-center {{ get('cat', 1) == 2 ? 'active' : '' }}" href="?cat=2">
                                <img src="{{ asset('assets/img/bangunan-'.(get('cat', 1) == 2 ? 'green' : 'black').'-icon.png') }}" data-inactive="{{ asset('assets/img/bangunan-'.(get('cat', 1) == 2 ? 'green' : 'black').'-icon.png') }}" data-active="{{ asset('assets/img/bangunan-green-icon.png') }}">
                                <div class="menu-icon"></div>
                                <p>Bangunan</p>
                            </a>
                            <a class="col-sm-2 text-center {{ get('cat', 1) == 3 ? 'active' : '' }}" href="?cat=3">
                                <img src="{{ asset('assets/img/keramaian-'.(get('cat', 1) == 3 ? 'green' : 'black').'-icon.png') }}" data-inactive="{{ asset('assets/img/keramaian-'.(get('cat', 1) == 3 ? 'green' : 'black').'-icon.png') }}" data-active="{{ asset('assets/img/keramaian-green-icon.png') }}">
                                <div class="menu-icon"></div>
                                <p>Keramaian</p>
                            </a>
                            <a class="col-sm-2 text-center {{ get('cat', 1) == 4 ? 'active' : '' }}" href="?cat=4">
                                <img src="{{ asset('assets/img/financial-'.(get('cat', 1) == 4 ? 'green' : 'black').'-icon.png') }}" data-inactive="{{ asset('assets/img/financial-'.(get('cat', 1) == 4 ? 'green' : 'black').'-icon.png') }}" data-active="{{ asset('assets/img/financial-green-icon.png') }}">
                                <div class="menu-icon"></div>
                                <p>Financial</p>
                            </a>
                            <a class="col-sm-2 text-center {{ get('cat', 1) == 5 ? 'active' : '' }}" href="?cat=5">
                                <img src="{{ asset('assets/img/swot-'.(get('cat', 1) == 5 ? 'green' : 'black').'-icon.png') }}" data-inactive="{{ asset('assets/img/swot-'.(get('cat', 1) == 5 ? 'green' : 'black').'-icon.png') }}" data-active="{{ asset('assets/img/swot-green-icon.png') }}">
                                <div class="menu-icon"></div>
                                <p>SWOT</p>
                            </a>
                            <a class="col-sm-2 text-center {{ get('cat', 1) == 6 ? 'active' : '' }}" href="?cat=6">
                                <img src="{{ asset('assets/img/photo-'.(get('cat', 1) == 6 ? 'green' : 'black').'-icon.png') }}" data-inactive="{{ asset('assets/img/photo-'.(get('cat', 1) == 6 ? 'green' : 'black').'-icon.png') }}" data-active="{{ asset('assets/img/photo-green-icon.png') }}">
                                <div class="menu-icon"></div>
                                <p>Foto</p>
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="container-fluid">
                        <div class="row">
                            @foreach ($questions as $v)
                            <div class="col-sm-6 col-md-4" style="padding-top:20px;min-height:200px">
                                <h4>{{ $v['question'] }}</h4>
                                @foreach ($v['answer'] as $a) 
                                    @include('admin/mitra/survey/answer/' . strtolower($a['answers_type']), [
                                        'answer' => $a,
                                        'ref_mitras_id' => $mitra->id,
                                        'cms_users_id' => CRUDBooster::myId(),
                                        'ref_survey_questions_id' => $v['id'],
                                        'ref_survey_answers_id' => $a['id'],
                                        'answer_type' => $a['answers_type'],
                                        'current_value' => !empty($survey_value[$mitra->id][$a['id']]) ? $survey_value[$mitra->id][$a['id']] : ''
                                    ])
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    @if (get('cat', 1) == 1)
                    <a class="btn btn-success btn-lg" href="?cat={{get('cat', 1)+1}}" style="width:150px;">Berikutnya</a>                
                    @elseif (get('cat', 1) < 6 && get('cat', 1) > 1)
                    <a class="btn btn-default btn-lg" href="?cat={{get('cat', 1)-1}}" style="width:150px;">Sebelumnya</a>
                    <a class="btn btn-success btn-lg" href="?cat={{get('cat', 1)+1}}" style="width:150px;">Berikutnya</a>
                    @else
                    <a class="btn btn-default btn-lg" href="?cat={{get('cat', 1)-1}}" style="width:150px;">Sebelumnya</a>
                    <a class="btn btn-success btn-lg" href="?cat={{get('cat', 1)+1}}" style="width:150px;">Simpan Survei</a>
                    @endif
                </div>
            </div>
        </div>        
    </div>
</div>
<script>
    $(document).ready(function(){
        $(".survey-menu img").hover(function(){
            img = $(this).attr("data-active");
            $(this).attr("src", img);
        }, function(){
            img = $(this).attr("data-inactive");
            $(this).attr("src", img);
        });
    });

    $(".upload-action").change(function(event){
        
        id = $(this).attr('data-id');
        // srcimage = window.URL.createObjectURL($(this).files[0]);
        // $(".upload-area-preview-" + id).attr("src", srcimage);

        var reader = new FileReader();
        reader.onload = function(event){
            // var output = document.getElementById('output');
            // output.src = reader.result;
            console.log("reader", reader);
            $(".upload-area-preview-" + id).css("background-image", `url('`+reader.result+`')`);
            $(".upload-area-preview-" + id).css("background-size", "80%");
        };
        console.log("event.target.files", event.target.files);
        reader.readAsDataURL(event.target.files[0]);
    });

    function sendanswer(param, callback)
    {
        $.post('{{ route('post.survey.helper') }}', param, function(data){
            callback(data);
        })
    }
    </script>
@endsection