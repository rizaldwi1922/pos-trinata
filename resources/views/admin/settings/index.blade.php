@extends('layouts.admin.app')

@section('title', 'Pengaturan')

@section('content')

    @php
        $store = auth()->user()->store;
    @endphp

    <div class="card mt-4">
        <form action="{{ route('admin.settings.update') }}" class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Pengaturan</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="store_name" class="form-label">Nama Toko <span class="text-danger"> (Readonly)</span></label>
                    <input type="text" class="form-control" id="store_name" value="{{ $store->name }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="store_code" class="form-label">Kode Toko <span class="text-danger">
                            (Readonly)</span></label>
                    <input type="text" class="form-control" id="store_code" value="{{ $store->code }}" readonly>
                </div>

                <hr>

                <div class="mb-3">
                    <label for="receipt_logo" class="form-label">Tampil Logo Struk <span
                            class="text-danger">*</span></label>
                    <select class="form-select" id="receipt_logo" name="receipt_logo" required>
                        @php
                            $receiptOptions = [
                                0 => 'Tidak Tampil',
                                1 => 'Tampil',
                            ];
                        @endphp
                        @foreach ($receiptOptions as $key => $value)
                            <option value="{{ $key }}" @if (($settings['receipt_logo'] ?? null) == $key) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="receipt_logo_image" class="form-label">Logo Struk
                        @if (isset($settings['receipt_logo_image']))
                            <a href="@viewfile($settings['receipt_logo_image'])" target="_blank">(Lihat Logo)</a>
                        @endif
                    </label>
                    <input type="file" class="form-control" id="receipt_logo_image" name="receipt_logo_image">
                </div>

                <div class="mb-3">
                    <label for="receipt_logo_size" class="form-label">Ukuran Logo Struk (px)</label>
                    <input type="number" class="form-control" id="receipt_logo_size" name="receipt_logo_size"
                        value="{{ $settings['receipt_logo_size'] ?? null }}">
                </div>

                <hr>

                <div class="mb-3">
                    <label for="alert_stock_minimum" class="form-label">Alert Stok Minimal <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="alert_stock_minimum" name="alert_stock_minimum"
                        value="{{ $settings['alert_stock_minimum'] ?? null }}" required>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
