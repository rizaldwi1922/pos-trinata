@extends('layouts.admin.app')

@section('title', (isset($category) ? 'Edit' : 'Tambah') . ' Kategori Produk')

@section('content')

    <div class="card mt-4">
        <form action="{{ isset($category) ? route('admin.products.categories.update', $category->id) : route('admin.products.categories.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($category))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($category) ? 'Edit' : 'Tambah' }} Kategori Produk</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $category->name ?? '' }}"
                        required>
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
