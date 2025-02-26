@extends('layouts.admin.app')

@section('title', 'Detail Shift')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <h4 class="card-title
                d-flex align-items-center">Detail Shift</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Pegawai </label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $shift->user->name }}"
                    readonly>
            </div>

            <div class="mb-3">
                <label for="start_shift_at" class="form-label">Tanggal Mulai</label>
                <input type="text" class="form-control" id="start_shift_at" name="start_shift_at" value="{{ $shift->start_shift_at->translatedFormat('d F Y H:i') . ' WIB' }}"
                    readonly>
            </div>

            <div class="mb-3">
                <label for="end_shift_at" class="form-label
                    ">Tanggal Selesai</label>
                <input type="text" class="form-control" id="end_shift_at" name="end_shift_at" value="{{ $shift->end_shift_at ? ($shift->end_shift_at->translatedFormat('d F Y H:i') . ' WIB') : '-' }}"
                    readonly>
            </div>

            <div class="mb-3">
                <label for="amount_start" class="form-label">Saldo Awal</label>
                <input type="text" class="form-control" id="amount_start" name="amount_start" value="@currency($shift->amount_start)"
                    readonly>
            </div>

            <div class="mb-3">
                <label for="amount_end" class="form-label">Saldo Akhir</label>
                <input type="text" class="form-control" id="amount_end" name="amount_end" value="@currency($shift->amount_end)"
                    readonly>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h4 class="card-title
                d-flex align-items-center">Metode Pembayaran</h4>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Metode Pembayaran</th>
                        <th scope="col">Total Diterima</th>
                    </tr>
                </thead>
                @php
                    $grouped = $transactions->groupBy('payment_method_id');
                @endphp
                <tbody class="table-border-bottom-0">
                    @foreach ($grouped as $key => $group)
                        <tr>
                            <td class="no">{{ $loop->iteration }}</td>
                            <td>{{ $group[0]->paymentMethod->name }}</td>
                            <td>@currency($group->sum('amount_total'))</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h4 class="card-title
                d-flex align-items-center">Daftar Transaksi</h4>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Kode Transaksi</th>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Pembayaran</th>
                        <th scope="col">Hutang</th>
                        <th scope="col">Jumlah Total</th>
                        <th scope="col">Tanggal Waktu</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                @php
                    $total = 0;
                @endphp
                <tbody class="table-border-bottom-0">
                    @foreach ($transactions as $item)
                        @php
                            $total += $item->amount_total;
                        @endphp
                        <tr>
                            <td class="no">{{ $loop->iteration }}</td>
                            <td>{{ $item->trx_id }}</td>
                            <td>{{ $item->customer->name ?? '-' }}</td>
                            <td>{{ $item->paymentMethod->name }}</td>
                            <td>@currency($item->amount_less)</td>
                            <td>@currency($item->amount_total)</td>
                            <td>
                                {{ Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }} <small
                                    class="text-muted">{{ Carbon\Carbon::parse($item->created_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        data-bs-placement="top" title="Struk">
                                        <a href="{{ route('admin.transactions.show', $item->id) }}?from=dashboard" target="_blank"
                                            class="text-muted d-inline-block">
                                            <i class="bx bx-receipt"></i>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end"><b> Total</b></td>
                        <td><b>@currency($total)</b></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

@endsection
