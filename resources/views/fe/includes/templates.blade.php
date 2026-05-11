<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{ uri('kawan/img/favicon.ico') }}">

    <title>@yield('title')</title>
    <meta name="title" content="@yield('title')">
    <meta name="description" content="@yield('description')">
    <meta name="keywords" content="@yield('keyword')">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:image" content="@yield('image')">
    <meta property="og:url" content="{{getCurrentUrl()}}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('title')" />
    <meta name="twitter:description" content="@yield('description')" />
    <meta name="twitter:image" content="@yield('image')" />
    <meta name="twitter:image:src" content="@yield('image')" /> 
    <link rel="canonical" href="{{getCurrentUrl()}}" >

    <meta name="robots" content="index, follow" />
    <meta name="googlebot" content="index, follow" />
    <meta name="googlebot-news" content="index, follow" />
    <meta name="google-site-verification" content=""/>
    <link rel="dns-prefetch" href="//connect.facebook.net/">
    <link rel="preconnect" href="//connect.facebook.net/" crossorigin>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="mobile-web-app-capable" content='yes' >
    <meta name="apple-touch-fullscreen" content='yes' >
    <meta name="apple-mobile-web-app-capable" content='yes' >
    <meta name="apple-mobile-web-app-status-bar-style" content='default' >
    <meta name="theme-color" content="#0C9344">

    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "BlogPosting",
            "headline": "@yield('title')",
            "description": "@yield('description')",
            "keywords": [@yield('keyword')],
            "author": {
                "@type": "Person",
                "name": "Akhyar Maulana"
            },
            "url": "{{getCurrentUrl()}}",
            "mainEntityOfPage": "{{getCurrentUrl()}}",
            "datePublished": "2024-08-16", 
            "publisher": {
                "@type": "Organization",
                "name": "Akhyar Maulana",
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://akhyarmaulana.com/assets/img/logo-unusual-negative.png"
                }
            }
        }
    </script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Khand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{uri('kawan/css/style.css')}}?20250126084941">

    @yield('style')
    <style>
        body {
            padding-top: 56px; /* Adjust based on navbar height */
        }
    </style>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-7V9GQR6DG5"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-7V9GQR6DG5');
    </script>
    
</head>
<body>
    <!-- Navigation -->
    @include('fe/includes/nav')

    <!-- Main Content -->
    
        @yield('content')
    

    <!-- Footer -->
    @include('fe/includes/footer')

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        @yield('scripts')
    </script>

    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <!-- Initialize AOS -->
    <script>
        AOS.init();
    </script>


    <script>
        /* header on scroll */
        $(".main-nav").removeClass("nav-on-scroll");
        $(".main-logo-link").addClass("logo-on-scroll");
        
        let prevScrollPos = window.pageYOffset;
        if (window.pageYOffset == 0) {
            $(".main-logo-link").removeClass("logo-on-scroll");
        }

        window.onscroll = function() {
            const currentScrollPos = window.pageYOffset;

            if (prevScrollPos > currentScrollPos) {
                // Scrolling up
                $(".main-nav").removeClass("nav-on-scroll");
                if (currentScrollPos == 0) {
                    $(".main-logo-link").removeClass("logo-on-scroll");
                }
            } else {
                // Scrolling down
                $(".main-nav").addClass("nav-on-scroll");
                $(".main-logo-link").addClass("logo-on-scroll");
            }

            prevScrollPos = currentScrollPos;
        };

    </script>
</body>
</html>
