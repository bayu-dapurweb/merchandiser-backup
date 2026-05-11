@extends('fe/includes/templates', ['active_page' => 'cars'])

@section('title', 'Pilihan Mobil Sewa di Yogyakarta - Kawan Travelers')
@section('description', 'Temukan berbagai pilihan mobil sewa di Yogyakarta bersama Kawan Travelers. Kami menawarkan beragam kendaraan, mulai dari city car hingga SUV, yang siap memenuhi kebutuhan perjalanan Anda dengan harga kompetitif dan layanan profesional.')
@section('keyword', 'Sewa mobil Yogyakarta, Rental mobil Yogyakarta, Pilihan mobil sewa, Kawan Travelers, Sewa city car Yogyakarta, Sewa SUV Yogyakarta, Sewa mobil murah Yogyakarta, Penyewaan mobil Jogja, Sewa kendaraan Yogyakarta, Layanan sewa mobil profesional')
@section('image', uri('kawan/img/full-banner-pico.jpg'))


@section('style')
<link rel="stylesheet" href="{{uri('kawan/css/cars.css')}}">
@endsection

@section('content')

<div class="container-fluid sub-page-container">

    <div class="container">

        <div class="row">
            <div class="col-12">
                <h1 data-aos="fade-up" data-aos-duration="1500" data-aos-delay="300" class="page-main-title">Penyewaan Mobil</h1>
                <p data-aos="fade-up" data-aos-duration="1500" data-aos-delay="500">Rasakan kenyamanan maksimal di setiap perjalanan dengan mobil kami yang selalu bersih dan terawat.</p>
            </div>
        </div>

    </div>

</div>

<div class="container-fluid car-container">
    <div class="container" style="padding-top:130px;">
        <div class="row">
            @foreach ($cars as $k => $v)
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <a href="https://wa.me/6287865446460?text=Hi%2C%20Kawan..%20Saya%20ingin%20memesan%20mobil%20{{ $v['title']  }}%20untuk%20perjalanan%20tanggal...%20" target="_blank" class="cars-container">
                    <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="car-see-more" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 100}}">
                    <div class="cars-bg-ornament" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 500}}">
                        <img src="{{uri($v['image'])}}" alt="{{$v['title']}}" class="main-image" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 1000}}">
                    </div>
                    <div class="cars-desk-container">
                        <h3 data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 1200}}">{{$v['title']}}</h3>
                        <h4 data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 1200}}">Mulai dari <span>Rp{{nominal($v['price'])}}</span></h4>
                        <div class="desc mt-3" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 1700}}">
                            <p class="mb-0">Termasuk Dalam Paket</p>
                            <ul>
                                @foreach($v['benefits'] as $r)
                                <li>{{$r}}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="w-100" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 30) + 2000}}">
                            <button class="btn btn btn-warning px-4">
                                <i class="fa-brands fa-whatsapp"></i> Pesan Sekarang
                            </button>
                        </div>
                    </div>
                    
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>


@endsection
