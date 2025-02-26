@extends('layouts.admin.app')

@php
    $is_create = !isset($ingredient);
@endphp

@section('title', 'Riwayat Penggunaan Bahan')

@section('content')


    <div class="card mt-4">
        <form class="form-control">
            <div class="card-header">
                <h4 class="card-title d-flex align-items-center">
                    Detail Bahan</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        <div class="mb-3">
                            <label class="form-label"><b>Nama Bahan</b></label>
                            <br>
                            {{ $productIngredient->name }}
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mb-3">
                            <label class="form-label"><b>Statuan</b></label>
                            <br>
                            {{ $productIngredient->unit->name }}
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mb-3">
                            <label class="form-label"><b>Stok Tersedia</b></label>
                            <br>
                            {{ App\Models\IngredientStock::where('ingredient_id', $productIngredient->id)->where(function ($query) {
                                    $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
                                })->sum('amount_available') }}
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mb-3">
                            <label class="form-label"><b>Kode Bahan</b></label>
                            <br>
                            {{ $productIngredient->code }}
                        </div>
                    </div>
                </div>

                <hr>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">ID Transaksi</th>
                                <th scope="col">Stok Digunakan</th>
                                <th scope="col">Stok Sebelum</th>
                                <th scope="col">Stok Sesudah</th>
                                <th scope="col">Waktu Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($transationLogs as $item)
                                @php
                                    $transaction = App\Models\Transaction::find($item->transaction_id);
                                @endphp
                                <tr>
                                    <td class="no">
                                        {{ ($transationLogs->currentpage() - 1) * $transationLogs->perpage() + $loop->index + 1 }}
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('admin.transactions.show', $transaction->id) }}">{{ $transaction->trx_id }}</a>
                                    </td>
                                    <td>{{ $item->amount }}</td>
                                    <td style="color: green;">{{ $item->amount_before }}</td>
                                    <td @if($item->amount_after < 0) style="color: red;" @endif>{{ $item->amount_after }}</td>
                                    <td>{{ Carbon\Carbon::parse($item->updated_at)->translatedFormat('d F Y') }}
                                        <small
                                            class="text-muted">{{ Carbon\Carbon::parse($item->updated_at)->format('H:i') }}
                                            WIB</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="footer">
                    @include('layouts.admin.partials.pagination', ['data' => $transationLogs])
                </div>
            </div>
        </form>
    </div>

@endsection
