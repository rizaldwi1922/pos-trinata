@extends('layouts.admin.app')

@section('title', (isset($paymentMethod) ? 'Edit' : 'Tambah') . ' Metode Pembayaran')

@section('content')

    <div class="card mt-4">
        <form
            action="{{ isset($paymentMethod) ? route('admin.payment-methods.update', $paymentMethod->id) : route('admin.payment-methods.store') }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($paymentMethod))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">
                    {{ isset($paymentMethod) ? 'Edit' : 'Tambah' }} Metode Pembayaran</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{ $paymentMethod->name ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code"
                        value="{{ $paymentMethod->code ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="checkbox" id="is_cash" name="is_cash"
                            {{ isset($paymentMethod) && $paymentMethod->is_cash ? 'checked' : '' }} value="1">
                        <label class="form-check-label" for="is_cash">Apakah Tunai?</label>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
