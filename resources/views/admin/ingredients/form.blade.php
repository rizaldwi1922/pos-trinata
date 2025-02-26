@extends('layouts.admin.app')

@php
    $is_create = !isset($ingredient);
@endphp

@section('title', (!$is_create ? 'Edit' : 'Tambah') . ' Bahan')

@section('content')


    <div class="card mt-4">
        <form
            action="{{ !$is_create ? route('admin.ingredients.update', $ingredient->id) : route('admin.ingredients.store') }}"
            class="form-control" method="POST">
            @csrf
            @if (!$is_create)
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">
                    {{ !$is_create ? 'Edit' : 'Tambah' }} Bahan</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $ingredient->name ?? '' }}" id="name"
                        name="name" required>
                </div>
                <div class="mb-3">
                    <label for="unit_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select" id="unit_id" name="unit_id" required>
                        <option value="">Pilih Satuan</option>
                        @foreach (App\Models\ProductUnit::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get() as $unit)
                            <option value="{{ $unit->id }}" @if (isset($ingredient) && $ingredient->unit_id == $unit->id) selected @endif>
                                {{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Harga Bahan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control number-format" value="{{ $ingredient->price ?? '' }}" id="price"
                        name="price" required>
                </div>
                <div class="mb-3">
                    <label for="code" class="form-label">Kode Seri (Kosongkan jika otomatis)</label>
                    <input type="text" class="form-control" id="code" name="code"
                        value="{{ $ingredient->code ?? '' }}" maxlength="10">
                    @if (isset($ingredient))
                        <a style="font-size: 12px;" href="javascript::void(0)"
                            onclick="openBarcode('{{ $ingredient->code }}')">Lihat Barcode</a>
                    @endif
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
