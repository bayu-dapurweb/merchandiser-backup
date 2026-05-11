@extends('fe/includes/templates', ['active_page' => 'about-us'])

@section('title', 'Tentang Kami - Kawan Travelers: Layanan Sewa Mobil Terpercaya di Yogyakarta')
@section('description', 'Kawan Travelers adalah penyedia layanan sewa mobil di Yogyakarta yang menawarkan berbagai pilihan kendaraan berkualitas dengan harga kompetitif. Kami berkomitmen memberikan pelayanan terbaik untuk kebutuhan transportasi Anda, baik untuk wisata, bisnis, maupun keperluan pribadi.')
@section('keyword', 'Sewa mobil Yogyakarta, Rental mobil Yogyakarta, Kawan Travelers, Layanan sewa mobil, Transportasi Yogyakarta, Sewa mobil wisata, Sewa mobil bisnis, Penyewaan mobil Jogja, Sewa kendaraan Yogyakarta, Sewa mobil terpercaya.')
@section('image', uri('kawan/img/full-banner-pico.jpg'))

@section('style')
<link rel="stylesheet" href="{{uri('kawan/css/about-us.css')}}">
@endsection

@section('content')

<div class="container-fluid main-about-us-container">

    <div class="container">

        <div class="row">
            <div class="col-12">
                <h1 data-aos="fade-up" data-aos-duration="1500" data-aos-delay="300" class="page-main-title">Tentang Kawan Travelers</h1>
                <p data-aos="fade-up" data-aos-duration="1500" data-aos-delay="500">Mari berkenalan lebih dekat dengan kami</p>
            </div>
        </div>

    </div>

</div>

<div class="container-fluid py-5">
    <div class="container about-us-intro">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-6 col-lg-6 text-center">

                <div class="px-5">
                    <img src="{{ uri('kawan/img/banner-about-us.png') }}" alt="about us banner" class="w-100" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="500">
                </div>

            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">

                <div class="px-5 main-about-us-desc">
                    <small data-aos="fade-left" data-aos-duration="1500" data-aos-delay="800">ABOUT US</small>

                    <h2 data-aos="fade-left" data-aos-duration="1500" data-aos-delay="700">Mari Traveling Bersama Kami</h2>

                    <p data-aos="fade-left" data-aos-duration="1500" data-aos-delay="800">
                        Kami adalah perusahaan tour dan travel yang siap menjadi teman setiamu dalam setiap petualangan. Apa pun tujuan wisatamu, kami akan memastikan pengalamanmu penuh kesan dan tak terlupakan. Dengan berbagai pilihan destinasi yang menarik, kami siap mengantarkanmu ke tempat-tempat indah yang selama ini hanya ada dalam imajinasi. Bersama kami, setiap perjalanan menjadi lebih dari sekadar liburan—ini adalah kesempatan untuk mengeksplorasi dunia dengan cara yang seru dan menyenangkan.
                    </p>
                    {{-- <p>Tak hanya itu, driver kami yang ramah dan berpengalaman siap menemanimu di setiap langkah perjalanan. Mereka tak sekadar mengantar, tetapi juga menjadi pemandu yang penuh perhatian, memberikan tips-tips liburan yang berguna untuk memaksimalkan kesenanganmu. Dengan layanan yang hangat dan profesional, kami berkomitmen untuk menjadikan setiap momen liburanmu nyaman, aman, dan pastinya penuh kebahagiaan.
                    </p> --}}
                </div>

            </div>
        </div>
    </div>
</div>

<div class="contianer-fluid pos-contianer">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="pos-title text-center" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="700">Kami Bersemangat Untuk Berpetualang Bersamamu<br> Menuju Destinasi Terbaik di Indonesia </h3>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <div class="pos-main-box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="700">
                    <img src="{{uri('kawan/img/salary.png')}}" alt="pos-icon">
                    <h4>Harga Yang Bersahabat</h4>
                    <p>Liburan impian tak perlu mahal, kami tawarkan pengalaman luar biasa dengan harga yang bersahabat. </p>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <div class="pos-main-box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="800">
                    <img src="{{uri('kawan/img/service.png')}}" alt="pos-icon">
                    <h4>Driver Yang Ramah</h4>
                    <p>Nikmati perjalanan tanpa khawatir, driver kami tidak hanya profesional tapi juga penuh senyum dan ramah.</p>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <div class="pos-main-box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="900">
                    <img src="{{uri('kawan/img/car-service.png')}}" alt="pos-icon">
                    <h4>Mobil Yang Nyaman</h4>
                    <p>Perjalanan jauh terasa menyenangkan dengan mobil nyaman yang siap mengantar Anda kemana saja.</p>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
