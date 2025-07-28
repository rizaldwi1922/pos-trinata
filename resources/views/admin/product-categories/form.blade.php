@extends('layouts.admin.app')

@section('title', (isset($category) ? 'Edit' : 'Tambah') . ' Kategori Produk')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <a href="/admin/product/categories" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                </svg>
                Kembali</a>
        </div>
        <form
            action="{{ isset($category) ? route('admin.products.categories.update', $category->id) : route('admin.products.categories.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($category))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">
                    {{ isset($category) ? 'Edit' : 'Tambah' }} Kategori Produk</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{ $category->name ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar</label>
                    <input class="form-control" type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
