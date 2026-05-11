<nav class="navbar navbar-expand-lg navbar-light fixed-top main-nav-container nav-on-scroll main-nav">
    <div class="container">
        <a class="navbar-brand main-logo-link" href="#" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="300">
            <img src="{{uri('kawan/img/main-logo-kawan-traveler.png')}}" alt="Logo Kawan Traveler">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse main-nav-desk" id="main-nav">
            <ul class="navbar-nav">
                <li class="nav-item" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="300">
                    <a class="nav-link {{ $active_page == 'home' ? 'active' : '' }}" href="{{ route('get.home') }}">Home</a>
                </li>
                <li class="nav-item" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="600">
                    <a class="nav-link {{ $active_page == 'about-us' ? 'active' : '' }}" href="{{route('get.about-us') }}">Tentang Kami</a>
                </li>
                <li class="nav-item" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="900">
                    <a class="nav-link {{ $active_page == 'cars' ? 'active' : '' }}" href="{{route('get.cars') }}">Sewa Mobil</a>
                </li>
                {{-- <li class="nav-item" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="900">
                    <a class="nav-link {{ $active_page == 'caleries' ? 'active' : '' }}" href="{{route('get.galleries') }}">Galleries</a>
                </li> --}}
                <li class="nav-item" data-aos="fade-right" data-aos-duration="1500" data-aos-delay="1200">
                    <a class="nav-link {{ $active_page == 'tours' ? 'active' : '' }}" href="{{route('get.tours') }}">Info Wisata</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="#">Kontak</a>
                </li> --}}
            </ul>
            <div class="sub-nav-bottom">
                <h4>Reach Us in Social Media</h4>
                <a href="">
                    <img data-aos="fade-right" data-aos-duration="1500" data-aos-delay="1500" src="{{uri('kawan/img/instagram-icons.png')}}" alt="Instagram Item" >
                </a>
                <a href="">
                    <img data-aos="fade-right" data-aos-duration="1500" data-aos-delay="1600" src="{{uri('kawan/img/youtube-icons.png')}}" alt="Youtube Item" >
                </a>
            </div>
        </div>
        <a href="https://wa.me/6287865446460" target="_blank" class="btn btn-warning float-right px-4 d-none d-sm-none d-md-block d-lg-block" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="300"><i class="fa-brands fa-whatsapp"></i> Hubungi Kami?</a>
    </div>
</nav>
