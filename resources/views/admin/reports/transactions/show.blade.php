@extends('layouts.admin.app')

@section('title', 'Laporan Penjualan')

@section('content')

    <style>
        @page {
            size: A3;
            margin: 0;
        }

        @media print {

            .app-navbar,
            .div_action {
                display: none;
            }
        }
    </style>

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Laporan Penjualan</h4>
                    @php
                        $start_format = Carbon\Carbon::parse($start_date)->translatedFormat('d F Y');
                        $end_format = Carbon\Carbon::parse($end_date)->translatedFormat('d F Y');
                    @endphp
                    @if ($start_format != $end_format)
                        <h6 class="text-muted">Periode: <b>{{ $start_format }}</b> s/d
                            <b>{{ $end_format }}</b>
                        </h6>
                    @else
                        <h6 class="text-muted">Periode: <b>{{ $start_format }}</b></h6>
                    @endif
                </div>
                <div class="col-md-6 text-end div_action">
                    <a href="{{ route('admin.reports.transactions.download', ['start_date' => $start_date, 'end_date' => $end_date]) }}"
                        class="btn btn-default">Download Excel</a>
                    <a class="btn btn-default" onclick="window.print()" style="color:white;">
                        PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div>
                <div class="table-responsive table-card mb-3">
                    <table class="table align-middle table-nowrap mb-0" id="customerTable">
                        <thead class="table-light">
                            <tr>
                                <td colspan="6" class="text-center"><b>Ringkasan Penjualan</b></td>
                            </tr>
                        </thead>
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 40px;">No.</th>
                                <th scope="col" style="width: 180px;">Tanggal</th>
                                <th scope="col" style="width: 210px;">Pelanggan</th>
                                <th scope="col">Jumlah Item</th>
                                <th scope="col">Laba Kotor</th>
                                <th scope="col">Laba Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @php
                                $amount_total = 0;
                                $amount_profit = 0;
                                $amount_item = 0;
                                $amount_expense = 0;
                            @endphp
                            @foreach ($data as $item)
                                @php
                                    $amount_total += $item['amount_total'];
                                    $amount_profit += $item['amount_profit'];
                                    $amount_item += $item['total_items'];
                                @endphp
                                <tr>
                                    <td class="no">{{ $loop->iteration }}</td>
                                    <td>{{ $item['created_at'] }}</td>
                                    <td>{{ !empty($item['customer']) ? $item['customer'] : '-' }}</td>
                                    <td>{{ $item['total_items'] }} Item</td>
                                    <td>@currency($item['amount_total'])</td>
                                    <td>@currency($item['amount_profit'])</td>
                                </tr>
                                <tr style="background-color: rgb(244, 244, 244);">
                                    <td colspan="3"></td>
                                    <td colspan="3">
                                        @foreach ($item['data'] as $obj)
                                            {{ $obj['name'] }} (x{{ $obj['amount'] }})<br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" class="text-center"><b>Ringkasan Pengeluaran</b></td>
                            </tr>
                            @foreach ($expenses as $item)
                                @php
                                    $amount_expense += $item['amount'];
                                @endphp
                                <tr style="background-color: rgb(244, 244, 244);">
                                    <td>{{ $loop->iteration }}</td>
                                    <td colspan="4">
                                        {{ $item['name'] }} (@currency($item['amount']))
                                    </td>
                                    <td>{{ $item['date'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5"><b>Total Pengeluaran</b></td>
                                <td><b>@currency($amount_expense)</b></td>
                            </tr>
                            <tr>
                                <td colspan="5"><b>Total Item Terjual</b></td>
                                <td><b>{{ $amount_item }} Item</b></td>
                            </tr>
                            <tr>
                                <td colspan="5"><b>Total Laba Kotor</b></td>
                                <td><b>@currency($amount_total)</b></b></td>
                            </tr>
                            <tr>
                                <td colspan="5"><b>Total Laba Bersih</b></td>
                                @php
                                    $amount_profit = $amount_profit - $amount_expense;
                                @endphp
                                @if ($amount_profit < 0)
                                    <td><b style="color: red;">-@currency(abs($amount_profit))</b></td>
                                @else
                                    <td><b>@currency($amount_profit)</b></td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
