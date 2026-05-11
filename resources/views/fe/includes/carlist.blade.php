<div class="container-fluid car-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <h1 data-aos="fade-up" data-aos-duration="800" data-aos-delay="900">Sewa Mobil</h1>
            </div>
        </div>
        <div class="row">
            @foreach ($cars as $k => $v)
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <a href="https://wa.me/6287865446460?text=Hi%2C%20Kawan..%20Saya%20ingin%20memesan%20mobil%20{{ $v['title']  }}%20untuk%20perjalanan%20tanggal...%20" target="_blank" class="cars-container">
                    <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="car-see-more" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 100}}">
                    <div class="cars-bg-ornament" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 500}}">
                        <img src="{{uri($v['image'])}}" alt="{{$v['title']}}" class="main-image" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 1000}}">
                    </div>
                    <div class="cars-desk-container">
                        <h3 data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 1200}}">{{$v['title']}}</h3>
                        <h4 data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 1200}}">Mulai dari <span>Rp{{nominal($v['price'])}}</span></h4>
                        <div class="desc mt-3" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 1700}}">
                            <p class="mb-0">Termasuk Dalam Paket</p>
                            <ul>
                                @foreach($v['benefits'] as $r)
                                <li>{{$r}}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="w-100" data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{($k * 300) + 2000}}">
                            <button class="btn btn btn-warning px-4">
                                <i class="fa-brands fa-whatsapp"></i> Pesan Sekarang
                            </button>
                        </div>
                    </div>
                    
                </a>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <button class="btn btn btn-warning px-4 btn-other-cars" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="{{($k * 300) + 2000}}">
                    <i class="fa fa-car"></i> Lihat Mobil Lainnya...
                </button>
            </div>
        </div>
    </div>
</div>