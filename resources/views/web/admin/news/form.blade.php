@extends('layouts.admin.app')

@section('title', (isset($news) ? 'Edit' : 'Tambah') . ' Blog')

@section('content')
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

    <div class="card mt-4">
        <form action="{{ isset($news) ? route('web.admin.news.update', $news->id) : route('web.admin.news.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($news))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($news) ? 'Edit' : 'Tambah' }}
                    Blog</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Judul <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ $news->title ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" required>{{ $news->content ?? '' }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar {!! isset($news) ? '' : '<span class="text-danger">*</span>' !!}</label>
                    <input class="form-control" type="file" id="image" name="image" accept="image/*"
                        {{ isset($news) ? '' : 'required' }}>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        CKEDITOR.replace('content');
    </script>
@endsection
