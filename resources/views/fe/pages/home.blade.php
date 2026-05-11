@extends('fe/includes/templates', ['active_page' => 'home'])

@section('title', 'Sewa Mobil di Yogyakarta - Nyaman, Terjangkau, dan Lengkap | Kawan Travelers')
@section('description', 'Cari sewa mobil di Yogyakarta? Kawan Travelers menyediakan berbagai pilihan kendaraan dengan harga terjangkau, layanan profesional, dan kenyamanan maksimal. Cocok untuk wisata, perjalanan bisnis, atau kebutuhan keluarga.')
@section('keyword', 'Sewa mobil Yogyakarta, Rental mobil Yogyakarta, Sewa mobil murah Yogyakarta, Sewa mobil wisata Yogyakarta, Rental mobil bandara YIA, Sewa mobil harian Yogyakarta, Sewa mobil dengan sopir Yogyakarta, Rental mobil lepas kunci Yogyakarta, Sewa kendaraan Yogyakarta, Jasa transportasi Yogyakarta.')
@section('image', uri('kawan/img/full-banner-pico.jpg'))

@section('content')

@include('fe/includes/banner')

<div class="container-fluid about-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 data-aos="fade-up" data-aos-duration="1500" data-aos-delay="500">Kawan Traveler</h1>
            </div>
            <div class="col-12 col-sm-12 col-md-7 col-lg-7 text-center">
                <p data-aos="fade-up" data-aos-duration="1500" data-aos-delay="1000">Sewa mobil murah di Jogja dan paket wisata seru di Yogyakarta! Kami punya berbagai pilihan mobil seperti Avanza, Xenia, Innova, Hiace, dan Elf, lengkap dengan sopir berpengalaman. Jelajahi Yogyakarta dengan paket wisata lengkap dan layanan ramah. Sewa Hiace atau Innova Reborn juga tersedia dengan harga terbaik!</p>
            </div>
        </div>
    </div>
</div>


@include('fe/includes/carlist')
@include('fe/includes/tourlist')

@endsection
