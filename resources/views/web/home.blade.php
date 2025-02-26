@extends('layouts.web.app')

@section('title', 'Beranda')

@section('content')

    <div class="container-fluid header position-relative overflow-hidden p-0">
        <!-- Hero Header Start -->
        <div class="hero-header overflow-hidden px-5">
            <div class="rotate-img">
                <img src="img/sty-1.png" class="img-fluid w-100" alt="">
                <div class="rotate-sty-2"></div>
            </div>
            <div class="row gy-5 align-items-center">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                    <h1 class="display-4 text-dark mb-4 wow fadeInUp" data-wow-delay="0.3s">Bisnis Mudah Untuk Semua</h1>
                    <p class="fs-4 mb-4 wow fadeInUp" data-wow-delay="0.5s">Point of Sale dengan Ragam fitur lengkap dan penyajian data akurat, pastikan strategi bisnis yang lebih tepat.</p>
                    <a href="#" class="btn btn-primary rounded-pill py-3 px-5 wow fadeInUp" data-wow-delay="0.7s">Get
                        Started</a>
                </div>
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.2s">
                    <img src="{{ asset('assets/img/hero-img-1.png') }}" class="img-fluid w-100 h-100" alt="">
                </div>
            </div>
        </div>
        <!-- Hero Header End -->
    </div>

    <!-- About Start -->
    <div class="container-fluid overflow-hidden py-5" style="margin-top: 6rem;">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="RotateMoveLeft">
                        <img src="{{ asset('assets/img/about-1.png') }}" class="img-fluid w-100" alt="">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                    <h4 class="mb-1 text-primary">About Us</h4>
                    <h1 class="display-5 mb-4">Get Started Easily With a Personalized Product Tour</h1>
                    <p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, suscipit itaque
                        quaerat dicta porro illum, autem, molestias ut animi ab aspernatur dolorum officia nam dolore.
                        Voluptatibus aliquam earum labore atque.
                    </p>
                    <a href="#" class="btn btn-primary rounded-pill py-3 px-5">About More</a>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Service Start -->
    @include('web.partials.section_features')
    <!-- Service End -->

    <!-- Pricing Start -->
    @include('web.partials.section_pricing')
    <!-- Pricing End -->

    <!-- Blog Start -->
    @include('web.partials.section_news', ['data' => \App\Models\News::orderBy('id', 'DESC')->limit(4)->get()])
    <!-- Blog End -->

@endsection
