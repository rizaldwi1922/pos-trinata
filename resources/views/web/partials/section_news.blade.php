<div class="container-fluid blog py-5">
    <div class="container py-5">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 900px;">
            <h4 class="text-primary">Our Blog</h4>
            @if (!isset($ignore) || isset($ignore) && !in_array('header', $ignore))
                <h1 class="display-5 mb-4">Join Us For New Blog</h1>
                <p class="mb-0">Dolor sit amet consectetur, adipisicing elit. Ipsam, beatae maxime. Vel animi eveniet
                    doloremque reiciendis soluta iste provident non rerum illum perferendis earum est architecto dolores
                    vitae quia vero quod incidunt culpa corporis, porro doloribus. Voluptates nemo doloremque cum.
                </p>
            @endif
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ($data as $news)
                <div class="col-md-6 col-lg-4 col-xl-3 wow fadeInUp" data-wow-delay="0.{{ $loop->iteration + ($loop->iteration + 1) }}s">
                    <div class="blog-item">
                        <div class="blog-img">
                            <img src="@viewfile($news->image, public)" class="img-fluid w-100" style="min-height: 210px;" alt="">
                            <div class="blog-info">
                                <span><i class="fa fa-clock"></i> {{ $news->created_at->translatedFormat('d F Y') }}</span>
                                {{-- <div class="d-flex">
                                    <span class="me-3"> 3 <i class="fa fa-heart"></i></span>
                                    <a href="#" class="text-white">0 <i class="fa fa-comment"></i></a>
                                </div> --}}
                            </div>
                        </div>
                        <div class="blog-content text-dark border p-4 ">
                            <h5 class="mb-4" style="cursor: pointer;" onclick="location.href='{{ route('web.news.detail', $news->slug) }}'">{{ $news->title }}</h5>
                            <div style="max-height: 100px; overflow: hidden;">
                                <p class="mb-4">{!! $news->content !!}</p>
                            </div>
                            <br>
                            <a class="btn btn-light rounded-pill py-2 px-4" href="{{ route('web.news.detail', $news->slug) }}">Read More</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
