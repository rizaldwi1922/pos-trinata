<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Rubik:wght@400;500&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="{{ asset('assets/vendor/libs/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/libs/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/libs/lightbox/css/lightbox.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/web-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/web-style.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/vendor/js/sweetalert.all.js') }}"></script>
</head>

<body>

    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    @php
        $ignoreNavbar = $ignoreNavbar ?? true;
    @endphp
    @if ($ignoreNavbar  )
        <div class="@if(!$ignoreNavbar) container-fluid header position-relative overflow-hidden p-0 @else container-fluid p-0 @endif">
            @include('layouts.web.navbar')
        </div>
    @endif

    @yield('content')

    {{-- @include('layouts.web.footer') --}}

    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center text-md-start mb-md-0">
                    <span class="text-white"><a href="#"><i class="fas fa-copyright text-light me-2"></i>Your Site
                            Name</a>, All right reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end text-white">
                    <!--/*** This template is free as long as you keep the below author’s credit link/attribution link/backlink. ***/-->
                    <!--/*** If you'd like to use the template without the below author’s credit link/attribution link/backlink, ***/-->
                    <!--/*** you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". ***/-->
                    Designed By <a class="border-bottom" href="https://htmlcodex.com">HTML Codex</a>
                </div>
            </div>
        </div>
    </div>

    <div class="wh-api">
        <div class="wh-fixed whatsapp-pulse">
            <a href="https://api.whatsapp.com/send?phone=0000000000000&text=hello world">
                <button class="wh-ap-btn"></button>
            </a>
        </div>
    </div>

    @include('sweetalert::alert')

    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/easing/easing.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/counterup/counterup.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/lightbox/js/lightbox.min.js') }}"></script>
    <script src="{{ asset('assets/js/web-main.js') }}"></script>
</body>

</html>
