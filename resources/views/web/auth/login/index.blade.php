@extends('layouts.admin.app')

@section('title', 'Login')

@section('content')
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card col-lg-4 col-md-8 col-sm-12">
            <div class="card-body">
                <div class="app-brand justify-content-center">
                    <img src="{{ asset('assets/img/ic_logo_new.png') }}" style="width: 140px; image-orientation: none;" alt="login_image">
                </div>
                <form action="{{ route('auth.login') }}" class="mb-3" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="text" class="form-control" id="email" name="email"
                            placeholder="Enter your email or username" required autofocus />
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="password">Password</label>
                            {{-- <a href="auth-forgot-password-basic.html">
                                <small>Forgot Password?</small>
                            </a> --}}
                        </div>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password" class="form-control" name="password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password" required/>
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" />
                            <label class="form-check-label" for="remember-me"> Ingat Saya </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-default d-grid w-100" type="submit">Masuk</button>
                    </div>
                </form>

                {{-- <p class="text-center">
                    <span>New on our platform?</span>
                    <a href="auth-register-basic.html">
                        <span>Create an account</span>
                    </a>
                </p> --}}
            </div>
        </div>
    </div>

@endsection
