@extends('fe/includes/templates', ['active_page' => 'tours'])

@section('title', $tour['seo']['title'])
@section('description', $tour['seo']['description'])
@section('keyword', $tour['seo']['keyword'])
@section('image', uri($tour['seo']['thumb_image']))


@section('style')
<link rel="stylesheet" href="{{uri('kawan/css/tour.css')}}">
@endsection

@section('content')

<div class="container-fluid sub-page-container">

    <div class="container">

        <div class="col-12">
            <h1 data-aos="fade-up" data-aos-duration="1500" data-aos-delay="300" class="page-main-title">{{ $tour['title'] }}</h1>
            <p data-aos="fade-up" data-aos-duration="1500" data-aos-delay="500">{{ $tour['location'] }}</p>
        </div>

    </div>

</div>

<div class="container-fluid tour-container">
    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                <img src="{{ uri($tour['main_image']) }}" alt="{{ $tour['title'] }}" class="main-image">
            </div>

            <div class="col-12 col-sm-12 col-md-8 col-lg-8 main-article">
                {!! $tour['body'] !!}
            </div>
        </div>

        @if (false)
        <div class="row justify-content-center">
            <div class="col-12">
                <h3 class="text-center">Our Advanture Galeries</h3>
            </div>
            <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                <div class="row">
                    <div class="col-4">
                        <div class="h-170"></div>
                    </div>
                    <div class="col-8">
                        <div class="h-170"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="h-170"></div>
                    </div>
                    <div class="col-4">
                        <div class="h-170"></div>
                    </div>
                    <div class="col-4">
                        <div class="h-170"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                <h4 class="text-left">Share Me...</h4>
            </div>
            <div class="col-12 col-sm-12 col-md-8 col-lg-8 mt-3">
                <a href="" class="share-button share-button-whatsapp"><i class="text-success fa-brands fa-whatsapp"></i></a>
                <a href="" class="share-button share-button-facebook"><i class="text-primary fa-brands fa-facebook"></i></a>
                <a href="" class="share-button share-button-twitter"><i class="text-info fa-brands fa-twitter"></i></a>
                <a href="" class="share-button share-button-link"><i class="text-warning fa fa-link"></i></a>
            </div>
        </div>

        @endif
        
    </div>
</div>

@endsection
