@extends('fe/includes/templates', ['active_page' => 'tours'])

@section('title', 'Paket Wisata Menarik di Yogyakarta dan Sekitarnya - Kawan Travelers')
@section('description', 'Jelajahi keindahan Yogyakarta dan destinasi sekitarnya dengan paket wisata eksklusif dari Kawan Travelers. Nikmati petualangan seru seperti mendaki Gunung Merapi, menjelajahi Puncak Gunung Bromo, dan menikmati pesona Pulau Dewata Bali. Segera hadir untuk memenuhi hasrat berwisata Anda')
@section('keyword', 'Paket wisata Yogyakarta, Tour Yogyakarta, Petualangan Gunung Merapi, Mendaki Gunung Bromo, Wisata Bali, Kawan Travelers, Paket tour Jogja, Wisata alam Yogyakarta, Tour and travel Yogyakarta, Destinasi wisata Indonesia.')
@section('image', uri('kawan/img/full-banner-pico.jpg'))


@section('style')
<link rel="stylesheet" href="{{uri('kawan/css/tours.css')}}">
@endsection

@section('content')

<div class="container-fluid sub-page-container">

    <div class="container">

        <div class="row">
            <div class="col-12">
                <h1 data-aos="fade-up" data-aos-duration="1500" data-aos-delay="300" class="page-main-title">Info Wisata
                    {{-- <span class="badge bg-danger">Coming Soon</span> --}}
                </h1>
                <p data-aos="fade-up" data-aos-duration="1500" data-aos-delay="500">Setiap destinasi jadi pengalaman berkesan tak terlupakan bersamamu.</p>
            </div>
        </div>

    </div>

</div>

<div class="container-fluid tour-container" style="background-image: url('{{ uri('kawan/img/negative-ornament-right.svg') }}')">
    <div class="container">
        
        <div class="row">
            @foreach ($tours as $v)
            <div class="col-12 col-sm-6 col-md-3 col-lg-4">
                <div class="tour-card-box">
                    <a href="{{ route('get.tour.detail', ['slug' => $v['slug']]) }}"><img src="{{uri($v['thum_image'])}}" alt="{{$v['title']}}"></a>
                    <div class="tour-card-mesage">
                        <a href="{{ route('get.tour.detail', ['slug' => $v['slug']]) }}"><h3>{{$v['title']}}</h3></a>
                        <span>{{$v['location']}}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
    </div>
</div>

@endsection
