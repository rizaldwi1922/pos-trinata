@extends('layouts.admin.app')

@section('title', (isset($unit) ? 'Edit' : 'Tambah') . ' Unit Produk')

@section('content')

    <div class="card mt-4">
        <form action="{{ isset($unit) ? route('admin.products.units.update', $unit->id) : route('admin.products.units.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($unit))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">{{ isset($unit) ? 'Edit' : 'Tambah' }} Unit Produk</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $unit->name ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="symbol" class="form-label">Simbol <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="symbol" name="symbol" value="{{ $unit->symbol ?? '' }}"
                        required>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
