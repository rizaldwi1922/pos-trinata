@extends('layouts.admin.app')

@section('title', (isset($purchasePayment) ? 'Edit' : 'Tambah') . ' Pembayaran')

@section('content')

    <div class="card mt-4">
        <form
            action="{{ isset($purchasePayment) ? route('admin.purchase-payments.update', [$purchase->id, $purchasePayment->id]) : route('admin.purchase-payments.store', $purchase->id) }}"
            class="form-control" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($purchasePayment))
                @method('PUT')
            @endif
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">
                    {{ isset($purchasePayment) ? 'Edit' : 'Tambah' }} Supplier</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        @foreach ($paymentMethods as $payment_method)
                            <option value="{{ $payment_method->id }}" @if (isset($purchasePayment) && $purchasePayment->payment_method_id == $payment_method->id) selected @endif>
                                {{ $payment_method->name }}</option>
                        @endforeach
                    </select>
                </div>
                @php
                    $totalPayment = App\Models\PurchasePayment::where('purchase_id', $purchase->id)->sum('amount');
                @endphp
                <div class="mb-3">
                    <label for="amount" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="text" class="form-control number-format" min="0" max="{{ $purchase->total - $totalPayment }}" id="amount" name="amount"
                        value="{{ $purchasePayment->amount ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date"
                        value="{{ $purchasePayment->email ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">Catatan</label>
                    <textarea class="form-control" id="note" name="note"></textarea>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection
