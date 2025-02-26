@extends('layouts.web.app')

@section('title', $news->title)

@section('content')
<div class="container-fluid blog py-5">
    <div class="container py-5">
        <div class="text-center mx-auto mb-5" style="max-width: 900px;">
            <h4 class="text-primary">Our Blog</h4>
            <h1 class="display-5 mb-4">{{ $news->title }}</h1>
        </div>
        <div class="row g-4 justify-content-center">
            <img src="@viewfile($news->image, public)" style="width: 80% !important;" class="img-fluid" alt="{{ $news->title }}">
        </div>
        <br> <br>
        <div class="row g-4 justify-content-center">
            {!! $news->content !!}
        </div>
    </div>
</div>

@endsection
