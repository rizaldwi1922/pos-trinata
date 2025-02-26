<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 px-lg-5 py-3 py-lg-0">
    <a href="{{ route('web.home') }}" class="navbar-brand p-0">
        <img src="{{ asset('assets/img/ic_logo_new.png') }}" width="100px" alt="Logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="fa fa-bars"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0">
            <a href="{{ route('web.home') }}" class="nav-item nav-link">Beranda</a>
            <a href="{{ route('web.features') }}" class="nav-item nav-link">Fitur</a>
            <a href="{{ route('web.pricing') }}" class="nav-item nav-link">Harga</a>
            <a href="{{ route('web.news') }}" class="nav-item nav-link">Blog</a>
        </div>
        <script>
            // add active class on navbar link
            const navbarLinks = document.querySelectorAll('.nav-item');
            navbarLinks.forEach((item) => {
                if (item.href == window.location.href) {
                    item.classList.add('active');
                }
            });
        </script>
        @guest
            <a href="{{ route('auth.login') }}" class="btn btn-light border border-primary rounded-pill text-primary py-2 px-4 me-4">Masuk</a>
        @endguest
        <a href="{{ route('web.register') }}" class="btn btn-primary rounded-pill text-white py-2 px-4">Daftar</a>
    </div>
</nav>
