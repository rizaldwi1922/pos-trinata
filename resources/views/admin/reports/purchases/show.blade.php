@extends('layouts.admin.app')

@section('title', 'Laporan Pembelian')

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
                        d-flex align-items-center">Laporan Pembelian</h4>
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
                    <a href="{{ route('admin.reports.purchases.download', ['start_date' => $start_date, 'end_date' => $end_date]) }}"
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
                                <th scope="col" style="width: 40px;">No.</th>
                                <th scope="col" style="width: 180px;">Tanggal</th>
                                <th scope="col" style="width: 210px;">Invoice ID</th>
                                <th scope="col">Jumlah Item</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @php
                                $amount_total = 0;
                                $amount_item = 0;
                            @endphp
                            @foreach ($data as $item)
                                @php
                                    $amount_total += $item['amount_total'];
                                    $amount_item += count($item['purchase_details']);
                                @endphp
                                <tr>
                                    <td class="no">{{ $loop->iteration }}</td>
                                    <td>{{ $item['created_at'] }}</td>
                                    <td>{{ $item['invoice_number'] }}</td>
                                    <td>{{ count($item['purchase_details']) }} Item</td>
                                    <td>@currency($item['amount_total'])</td>
                                </tr>
                                <tr style="background-color: rgb(244, 244, 244);">
                                    <td colspan="2"></td>
                                    <td>
                                        @php
                                            $suppliers = [];
                                            foreach ($item['purchase_details'] as $obj) {
                                                if (!in_array($obj->supplier_name, $suppliers)) {
                                                    $suppliers[] = $obj->supplier_name;
                                                }
                                            }
                                        @endphp
                                        @foreach ($suppliers as $supplier)
                                            {{ $supplier }}<br>
                                        @endforeach
                                    </td>
                                    <td colspan="2">
                                        @foreach ($item['purchase_details'] as $obj)
                                            @php
                                                $obj = (array) $obj;
                                                [$type, $id] = explode(',', $obj['id']);
                                            @endphp
                                            @if ($type == 'v')
                                                {{ $obj['product_name'] }} ({{ $obj['variant_measurement'] }}
                                                {{ $obj['unit_name'] }})
                                                (x{{ $obj['amount_added'] }})
                                                <br>
                                            @else
                                                {{ $obj['ingredient_name'] }} ({{ $obj['unit_name'] }})
                                                (x{{ $obj['amount_added'] }})<br>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3"><b>Total</b></td>
                                <td><b>{{ $amount_item }} Item</b></td>
                                <td><b>@currency($amount_total)</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
