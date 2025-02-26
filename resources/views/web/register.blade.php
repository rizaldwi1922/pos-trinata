@extends('layouts.web.app')

@section('title', 'Daftar')

@section('content')

<div class="container-fluid service py-5">
    <div class="container py-5">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 900px;">
            <h4 class="mb-1 text-primary">Daftar</h4>
            <h1 class="display-5 mb-4">Mari Bergabung Dengan Kami</h1>
            <p class="mb-0">Dolor sit amet consectetur, adipisicing elit. Ipsam, beatae maxime. Vel animi eveniet
                doloremque reiciendis soluta iste provident non rerum illum perferendis earum est architecto dolores
                vitae quia vero quod incidunt culpa corporis, porro doloribus. Voluptates nemo doloremque cum.
            </p>
        </div>
        <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
            <div class="register-form">
                <h4 class="mb-4 text-center">Tim Kami Akan Menghubungi Anda</h4>
                <form action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required />
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" required />
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Nomor Telepon</label>
                        <input type="phone" class="form-control" id="phone" name="phone" required />
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Tulis kesulitan atau kebutuhan anda</label>
                        <textarea type="password" class="form-control" id="description"
                            name="description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-light rounded-pill col-12 text-primary py-2 px-4">Daftar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection
