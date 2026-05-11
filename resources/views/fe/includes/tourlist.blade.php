<div class="container-fluid tour-container" style="background-image: url('{{ uri('kawan/img/negative-ornament-right.svg') }}'); padding-top:30px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="500">
                <h1>Info Wisata</h1>
                <img src="{{ uri('kawan/img/coming-soon-red-sm.png') }}" alt="Coming Soon Icon" class="title-main-comming-soon">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-md-12">
                        <a class="tour-card" href="{{route('get.tour.detail', ['slug' => $tours[0]['slug']])}}" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="500">
                            <img src="{{uri($tours[0]['thum_image'])}}" alt="{{$tours[0]['title']}}">
                            <div class="tour-card-desc">
                                <div class="rour-card-text-container">
                                    <h3>{{$tours[0]['title']}}</h3>
                                    <span><i class="fas fa-map-marker-alt"></i> {{$tours[0]['location']}}</span>
                                    <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="tour-see-more">
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-md-6">
                        <a class="tour-card" href="{{route('get.tour.detail', ['slug' => $tours[1]['slug']])}}" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="800">
                            <img src="{{uri($tours[1]['thum_image'])}}" alt="{{$tours[1]['title']}}">
                            <div class="tour-card-desc">
                                <div class="rour-card-text-container">
                                    <h3>{{$tours[1]['title']}}</h3>
                                    <span><i class="fas fa-map-marker-alt"></i> {{$tours[1]['location']}}</span>
                                    <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="tour-see-more">
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-md-6">
                        <a class="tour-card" href="{{route('get.tour.detail', ['slug' => $tours[2]['slug']])}}" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="1200">
                            <img src="{{uri($tours[2]['thum_image'])}}" alt="{{$tours[2]['title']}}">
                            <div class="tour-card-desc">
                                <div class="rour-card-text-container">
                                    <h3>{{$tours[2]['title']}}</h3>
                                    <span><i class="fas fa-map-marker-alt"></i> {{$tours[2]['location']}}</span>
                                    <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="tour-see-more">
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-6 col-md-6">
                        {{-- <div class="tour-card-long"></div> --}}
                        <a class=" tour-card tour-card-long" href="{{route('get.tour.detail', ['slug' => $tours[3]['slug']])}}" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="600">
                            <img src="{{uri($tours[3]['thum_image'])}}" alt="{{$tours[3]['title']}}">
                            <div class="tour-card-desc">
                                <div class="rour-card-text-container">
                                    <h3>{{$tours[3]['title']}}</h3>
                                    <span><i class="fas fa-map-marker-alt"></i> {{$tours[3]['location']}}</span>
                                    <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="tour-see-more">
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-md-6">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-md-12">
                                <a class="tour-card" href="{{route('get.tour.detail', ['slug' => $tours[4]['slug']])}}" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="900">
                                    <img src="{{uri($tours[4]['thum_image'])}}" alt="{{$tours[4]['title']}}">
                                    <div class="tour-card-desc">
                                        <div class="rour-card-text-container">
                                            <h3>{{$tours[4]['title']}}</h3>
                                            <span><i class="fas fa-map-marker-alt"></i> {{$tours[4]['location']}}</span>
                                            <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="tour-see-more">
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-12 col-sm-12 col-md-12 col-md-12">
                                <a class="tour-card" href="{{route('get.tour.detail', ['slug' => $tours[5]['slug']])}}" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="1200">
                                    <img src="{{uri($tours[5]['thum_image'])}}" alt="{{$tours[5]['title']}}">
                                    <div class="tour-card-desc">
                                        <div class="rour-card-text-container">  
                                            <h3>{{$tours[5]['title']}}</h3>
                                            <span><i class="fas fa-map-marker-alt"></i> {{$tours[5]['location']}}</span>
                                            <img src="{{ uri('kawan/img/see-more-icon.svg') }}" alt="See more" class="tour-see-more">
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- <div class="col-12 col-sm-12 col-md-3 col-md-3">
                <div class="sample-card"></div>
            </div>
            <div class="col-12 col-sm-12 col-md-3 col-md-3">
                <div class="sample-card"></div>
            </div> --}}
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <button class="btn btn btn-primary btn-primary-suprime px-4" style="margin-top:60px;" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="{{($k * 300) + 2000}}">
                    <i class="fas fa-map-marker-alt"></i> Lihat Tour Lainnya...
                </button>
            </div>
        </div>
    </div>
</div>