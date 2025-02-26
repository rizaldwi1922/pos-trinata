@extends('layouts.web.app')

@section('title', 'Blog')

@section('content')
    <!-- Service Start -->
        @include('web.partials.section_news', ['data' => \App\Models\News::orderBy('id', 'DESC')->get(), 'ignore' => ['header']])
    <!-- Service End -->
@endsection
