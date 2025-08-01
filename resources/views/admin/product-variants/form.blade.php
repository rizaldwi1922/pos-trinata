@extends('layouts.admin.app')

@php
    $is_create = !isset($variant);
@endphp

@section('title', (!$is_create ? 'Edit' : 'Tambah') . ' Produk Varian')

@section('content')
    <div class="card mt-4">
        <div class="card-header">
            <a href="/admin/product/variants" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                </svg>
                Kembali</a>
        </div>
        <form
            action="{{ !$is_create ? route('admin.products.variants.update', $variant->id) : route('admin.products.variants.store') }}"
            class="form-control" method="POST">
            @csrf
            @if (!$is_create)
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">
                    {{ !$is_create ? 'Edit' : 'Tambah' }} Produk Varian</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    @if (!$is_create)
                        <input type="text" class="form-control" value="{{ $variant->product->name ?? '' }}" disabled>
                    @else
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">Pilih Produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @if (!$is_create && $variant->product_id == $product->id) selected @endif>
                                    {{ $product->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="mb-3">
                    <div class="variants">
                        @if (!$is_create)
                            @include('admin.products.partials.input_variant', [
                                'variant' => $variant,
                                'delete' => $is_create,
                                'divider' => $is_create,
                            ])
                        @else
                            @include('admin.products.partials.input_variant', [
                                'delete' => $is_create,
                                'divider' => $is_create,
                            ])
                        @endif
                    </div>
                    @if ($is_create)
                        <button type="button" class="btn btn-sm btn-dark col-12" id="add-variant">Tambah Varian</button>
                    @endif
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
