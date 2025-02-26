@extends('layouts.admin.app')

@section('title', 'Stok Opname')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Stok Opname</h4>
                </div>
            </div>
            <form class="mt-2" action="" method="GET">
                <div class="row">
                    @if (count($data) > 0)
                        <div class="col-md-6">
                            <div class="form-group d-flex align-items-center">
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Pencarian ...">
                            </div>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-group d-flex align-items-center">
                            <input type="date" class="form-control" id="date" name="filter_date"
                                value="{{ $filter_date }}" onchange="setFilter()">
                        </div>
                    </div>
                    @if (count($data) > 0 && $blade_user->hasPermissionTo('can_create_admin_daily_stocks'))
                        <div class="col-md-2">
                            <div class="form-group d-flex align-items-center">
                                <button type="button" class="btn btn-danger col-12" id="generate">Tarik Data
                                    Ulang</button>
                            </div>
                        </div>
                    @endif
                    <script>
                        function setFilter() {
                            var date = document.getElementById('date').value;
                            window.location.href = '{{ route('admin.daily-stocks.index') }}?filter_date=' + date;
                        }
                    </script>
                </div>
            </form>
        </div>
        @if (count($data) > 0)
            <div class="card-body">
                <p>
                    Ditarik Pada: <b>{{ Carbon\Carbon::parse($data[0]->created_at)->translatedFormat('d F Y H:i') }} WIB</b> <br>
                    Ditarik Oleh: <b>{{ App\Models\User::find($data[0]->user_id)->name }}</b>
                </p>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Produk</th>
                            <th scope="col">Varian</th>
                            <th scope="col">Stok Terdata</th>
                            <th scope="col">Stok Fisik</th>
                            <th scope="col">Balance</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($data as $item)
                            <tr>
                                <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}
                                </td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->measurement . ' / ' . $item->unit_name }} <br> <small
                                        class="text-muted">{{ $item->code }}</small></td>
                                <td>{{ $item->amount_start }}</td>
                                <td @if ($item->amount_end && $item->amount_end != $item->amount_start) class="text-danger" @endif>
                                    @if ($item->amount_end)
                                        {{ $item->amount_end }} <br> <small class="text-muted">oleh
                                            {{ App\Models\User::find($item->user_id)->name }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($item->amount_end)
                                        {{ abs($item->amount_start - $item->amount_end) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <ul class="list-inline hstack gap-2 mb-0">
                                        @include('layouts.admin.partials.act_edit', [
                                            'title' => 'Stok Opname',
                                            'href' => route('admin.daily-stocks.edit', $item->id),
                                            'permission' => 'daily_stocks',
                                        ])
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="footer">
                @include('layouts.admin.partials.pagination', ['data' => $data])
            </div>
        @else
            @if ($blade_user->hasPermissionTo('can_create_admin_daily_stocks'))
                <div class="card-body">
                    <p>Belum ada stok terdata</p>
                    <div class="mb-3">
                        <button type="button" class="btn btn-default" id="generate">Tarik Data</button>
                    </div>
                </div>
            @endif
        @endif
        <script>
            document.getElementById('generate').addEventListener('click', function() {
                Swal.fire({
                    title: 'Tarik Data',
                    text: 'Apakah Anda yakin ingin menarik data?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Tidak',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('generate_form').submit();
                    }
                });
            });
        </script>
        <form action="{{ route('admin.daily-stocks.store') }}" id="generate_form" method="POST">
            @csrf
            <input type="hidden" name="filter_date" value="{{ $filter_date }}">
        </form>

    </div>

@endsection
