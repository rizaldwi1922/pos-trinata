@extends('layouts.admin.app')

@section('title', 'Dashboard')

@section('content')

    @pushOnce('scripts')
        <script src="{{ asset('assets/js/chart.min.js') }}"></script>

        @php
            $labels = [];
            foreach ($payload['arr_chart_data'] as $value) {
                $labels[] = $value['date'];
            }

            $data = [];
            foreach ($payload['arr_chart_data'] as $value) {
                $data[] = $value['total'];
            }

            $labels = array_reverse($labels);
            $data = array_reverse($data);

            $lineData = [];
            for ($i = 0; $i < count($data); $i++) {
                if ($i == 0) {
                    $lineData[] = 0;
                } else {
                    if ($data[$i - 1] == 0) {
                        $lineData[] = 0;
                    } else {
                        $lineData[] = round((($data[$i] - $data[$i - 1]) / $data[$i - 1]) * 100, 2);
                    }
                }
            }
        @endphp

        <script>
            let canvas_buy = document.getElementById("canvas_transaction");

            let buyChart = new Chart(canvas_buy, {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: [{
                            label: "Total Penjualan",
                            backgroundColor: "rgba(54, 162, 235, 0.5)",
                            borderColor: "rgba(2,117,216,1)",
                            data: @json($data),
                            yAxisID: 'y',
                            order: 0,
                            type: 'bar'
                        },
                        {
                            label: "Persentase (%)",
                            backgroundColor: "rgba(75, 192, 192, 0.4)",
                            borderColor: "rgba(75, 192, 192, 1)",
                            data: @json($lineData),
                            yAxisID: 'y1',
                            type: 'line',
                            fill: false,
                            order: 1
                        }
                    ],
                },
                options: {
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Penjualan'
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Persentase (%)'
                            }
                        }
                    }
                }
            });
        </script>
    @endPushOnce

    @php
        $listFilter = ['Harian', 'Bulanan'];
        $listFilterLabel = [
            'Harian' => 'Hari Ini',
            'Bulanan' => '30 Hari Terakhir',
        ];
    @endphp

    <div class="flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 order-1">
                <div class="row">
                    <div class="col-md-3 col-sm-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-receipt" style="font-size: 30px;"></i>
                                    </div>
                                    @if ($blade_user->hasPermissionTo('can_view_admin_transactions'))
                                        <div class="form-group">
                                            <select class="form-select" id="filter_today_transaction"
                                                name="filter_today_transaction"
                                                onchange="insertParam('filter_today_transaction', this.value)">
                                                @foreach ($listFilter as $value)
                                                    <option value="{{ $value }}"
                                                        @if (session('filter_today_transaction', 10) == $value) selected @endif>
                                                        {{ $listFilterLabel[$value] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                <span class="d-block mb-1">Total Transaksi</span>
                                <h3 class="card-title text-nowrap mb-2">{{ $payload['s_amount_transaction'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-sm-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-bar-chart" style="font-size: 30px;"></i>
                                    </div>
                                    @if ($blade_user->hasPermissionTo('can_view_admin_transactions'))
                                        <div class="form-group">
                                            <select class="form-select" id="filter_transaction" name="filter_transaction"
                                                onchange="insertParam('filter_transaction', this.value)">
                                                @foreach ($listFilter as $value)
                                                    <option value="{{ $value }}"
                                                        @if (session('filter_transaction', 10) == $value) selected @endif>
                                                        {{ $listFilterLabel[$value] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                <span class="d-block mb-1">Total Penjualan</span>
                                <h3 class="card-title text-nowrap mb-2">@currency($payload['s_total_transaction'])</h3>
                                {{-- <small class="text-danger fw-medium"><i class="bx bx-down-arrow-alt"></i> -14.82%</small> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <i class="bx bx-money" style="font-size: 30px;"></i>
                                    </div>
                                    @if ($blade_user->hasPermissionTo('can_view_admin_transactions'))
                                        <div class="form-group">
                                            <select class="form-select" id="filter_profit" name="filter_profit"
                                                onchange="insertParam('filter_profit', this.value)">
                                                @foreach ($listFilter as $value)
                                                    <option value="{{ $value }}"
                                                        @if (session('filter_profit', 10) == $value) selected @endif>
                                                        {{ $listFilterLabel[$value] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                <span class="d-block mb-1">Total Laba</span>
                                <h3 class="card-title text-nowrap mb-2">@currency($payload['s_total_profit'])</h3>
                                {{-- <small class="text-danger fw-medium"><i class="bx bx-down-arrow-alt"></i> -14.82%</small> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-12 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Grafik Penjualan</h5>
                        @if ($blade_user->hasPermissionTo('can_view_admin_transactions'))
                            <div class="form-group">
                                <select class="form-select" id="filter_graph_transaction" name="filter_graph_transaction"
                                    onchange="insertParam('filter_graph_transaction', this.value)">
                                    @foreach ($listFilter as $value)
                                        <option value="{{ $value }}"
                                            @if (session('filter_graph_transaction', 10) == $value) selected @endif>
                                            {{ $listFilterLabel[$value] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <canvas id="canvas_transaction" width="100%" height="35"></canvas>
                    </div>
                </div>
            </div>

            @php
                $stockMinimum = (int) App\Models\Setting::where('key', 'alert_stock_minimum')
                    ->where('store_id', $blade_user->store_id)
                    ->value('value');
            @endphp

            <div class="col-12 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Stok akan Habis</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Tipe</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Sisa Stok</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @foreach ($payload['low_stock_items'] as $item)
                                        <tr>
                                            <td>{{ ($payload['low_stock_items']->currentpage() - 1) * $payload['low_stock_items']->perpage() + $loop->index + 1 }}</td>
                                            <td>{{ !empty($item->ingredient_id) ? 'Bahan' : 'Varian' }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td @if ($item->total_stock < $stockMinimum) class="text-danger" @endif>
                                                {{ $item->total_stock }}
                                            </td>
                                            <td>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="footer">
                            @include('layouts.admin.partials.pagination', [
                                'data' => $payload['low_stock_items'],
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-6 col-sm-12 order-2 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2">Jumlah Transaksi</h5>
                            <small class="text-muted">Data Keseluruhan</small>
                        </div>
                        {{-- <div class="dropdown">
                            <button class="btn p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
                                <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                <a class="dropdown-item" href="javascript:void(0);">Share</a>
                            </div>
                        </div> --}}
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex flex-column gap-1">
                                <h2 class="mb-2">{{ $payload['s_amount_transaction_all'] }}</h2>
                                <span>Produk Terlaris</span>
                            </div>
                            <div id="orderStatisticsChart"></div>
                        </div>
                        <ul class="p-0 m-0">
                            @foreach ($payload['arr_transactions'] as $item)
                                <li class="d-flex mb-3 pb-1">
                                    <div class="avatar flex-shrink-0 me-3">
                                        @php
                                            $image = $item['product_image'];
                                            $is_url = filter_var($image, FILTER_VALIDATE_URL);
                                        @endphp
                                        @if ($image)
                                            @if ($is_url)
                                                <img src="{{ $image }}" alt="{{ $item['product_name'] }}"
                                                    class="img-fluid"
                                                    onerror="this.src='{{ asset('assets/app/pos/images/product-default.png') }}'"
                                                    width="50px">
                                            @else
                                                <img src="@viewfile($image)" alt="{{ $item['product_name'] }}"
                                                    class="img-fluid"
                                                    onerror="this.src='{{ asset('assets/app/pos/images/product-default.png') }}'"
                                                    width="50px">
                                            @endif
                                        @else
                                            <img src="{{ asset('assets/app/pos/images/product-default.png') }}"
                                                alt="{{ $item['product_name'] }}" class="img-fluid" width="50px">
                                        @endif
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">{{ $item['product_name'] }}</h6>
                                            <small class="text-muted">{{ $item['category_name'] }}</small>
                                        </div>
                                        <div class="user-progress">
                                            <small class="fw-medium">{{ $item['total'] }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-6 col-sm-12 order-2 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0 me-2">Transaksi Terbaru</h5>
                        {{-- <div class="dropdown">
                            <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                                <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                                <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                            </div>
                        </div> --}}
                    </div>
                    <div class="card-body">
                        <ul class="p-0 m-0">
                            @foreach ($payload['arr_last_transactions'] as $item)
                                <a href="{{ route('admin.transactions.show', $item['id']) }}?from=dashboard"
                                    target="_blank">
                                    <li class="d-flex mb-4 pb-1">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="text-dark">{{ $loop->iteration }}</span>
                                        </div>
                                        <div
                                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <small class="text-muted d-block mb-1">{{ $item['trx_id'] }}</small>
                                                <h6 class="mb-0 {{ !$item['customer_id'] ? 'text-primary' : '' }}">
                                                    {{ $item['customer_id'] ? App\Models\Customer::find($item['customer_id'])->name : 'Guest' }}
                                                </h6>
                                            </div>
                                            <div class="user-progress d-flex align-items-center gap-1">
                                                <h6 class="mb-0">@currency($item['amount_total'])</h6>
                                            </div>
                                        </div>
                                    </li>
                                </a>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
