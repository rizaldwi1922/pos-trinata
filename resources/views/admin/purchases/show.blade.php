@extends('layouts.admin.app')

@section('title', 'Detail Pembelian')

@section('content')

    <div class="card mt-4">
        <form class="form-control">
            <div class="card-header">
                <h4 class="card-title
                d-flex align-items-center">
                    Detail Pembelian</h4>
            </div>
            @php
                $totalPayment = App\Models\PurchasePayment::where('purchase_id', $purchase->id)->sum('amount');
            @endphp
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label"><b>Invoice ID</b></label>
                            <br>
                            {{ $purchase->invoice_number }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label"><b>Status</b></label>
                            <br>
                            @if ($totalPayment >= $purchase->total)
                                <span class="badge bg-success">Lunas</span>
                            @else
                                <span class="badge bg-danger">Belum Lunas</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label"><b>Tanggal</b></label>
                            <br>
                            {{ Carbon\Carbon::parse($purchase->created_at)->translatedFormat('d F Y') }} WIB
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label"><b>Jatuh Tempo</b></label>
                            <br>
                            {{ $purchase->due_date ? Carbon\Carbon::parse($purchase->due_date)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label"><b>Total</b></label>
                            <br>
                            @currency($purchase->total)
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Supplier</th>
                                <th scope="col">Produk</th>
                                <th scope="col">Varian</th>
                                <th scope="col">Jumlah / Tersedia</th>
                                <th scope="col">Kadaluwarsa pada</th>
                            </tr>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($purchaseDetails as $item)
                                <tr>
                                    <td class="no">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $item->supplier_name }}
                                    <td>
                                        {{ $item->product_name ?? $item->ingredient_name }} @if ($item->code)
                                            <br> <small class="text-muted">{{ $item->code }}</small>
                                        @endif
                                    </td>
                                    <td>{{ isset($item->product_name) ? $item->variant_measurement . ' / ' . $item->unit_name : $item->unit_name }}
                                    </td>
                                    <td>{{ $item->amount_added . ' / ' . $item->amount_available }}</td>
                                    <td>{{ $item->expired_at ? Carbon\Carbon::parse($item->expired_at)->translatedFormat('d F Y') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    @if ($blade_user->hasPermissionTo('can_view_admin_purchase_payments'))
        <div class="card mt-4">
            <form class="form-control">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="card-title d-flex align-items-center">Detail Pembayaran</h4>
                        </div>
                        @if ($purchase->total > $totalPayment)
                            @if ($blade_user->hasPermissionTo('can_create_admin_purchase_payments'))
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('admin.purchase-payments.create', $purchase->id) }}"
                                        class="btn btn-default"><i class="bx bx-plus"></i>
                                        &nbsp;Tambah Pembayaran</a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-12">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Oleh</th>
                                    <th scope="col">Metode Pembayaran</th>
                                    <th scope="col">Jumlah</th>
                                    <th scope="col">Catatan</th>
                                    <th scope="col">Ditambahkan Pada</th>
                                    <th scope="col"></th>
                                </tr>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($purchasePayments as $item)
                                    <tr>
                                        <td class="no">
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            {{ $item->user->name }}
                                        </td>

                                        <td>
                                            {{ $item->paymentMethod->name }}
                                        </td>

                                        <td>
                                            @currency($item->amount)
                                        </td>

                                        <td>
                                            {{ $item->note ?? '-' }}
                                        </td>

                                        <td>
                                            {{ Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }} WIB
                                        </td>

                                        @if ($purchase->total > $totalPayment)
                                            <td>
                                                @include('layouts.admin.partials.act_delete', [
                                                    'title' => 'Pembayaran',
                                                    'href' => route('admin.purchase-payments.destroy', $item->id),
                                                    'permission' => 'purchase_payments',
                                                ])
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach

                                @if (count($purchasePayments) == 0)
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    @endif

@endsection
