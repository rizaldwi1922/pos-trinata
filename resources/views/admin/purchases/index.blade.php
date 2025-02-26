@extends('layouts.admin.app')

@section('title', 'Daftar Pembelian')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Pembelian</h4>
                </div>
                @if ($blade_user->hasPermissionTo('can_create_admin_purchases'))
                    <div class="col-md-6 text-end">
                        <a href="{{ route('admin.purchases.create') }}" class="btn btn-default"><i class="bx bx-plus"></i>
                            &nbsp;Tambah Pembelian</a>
                    </div>
                @endif
            </div>
            <form class="mt-2" action="" method="GET">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group d-flex align-items-center">
                            <input type="text" class="form-control" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Pencarian ...">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Invoice ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Jatuh Tempo</th>
                        <th scope="col">Tanggal Pembelian</th>
                        <th scope="col">Terakhir diperbaharui</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        @php
                            $totalPayment = App\Models\PurchasePayment::where('purchase_id', $item->id)->sum('amount');
                        @endphp
                        <tr>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->invoice_number }}</td>
                            <td>
                                @if ($totalPayment >= $item->total)
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-danger">Belum Lunas</span>
                                @endif
                            </td>
                            <td>{{ Carbon\Carbon::parse($item->due_date)->translatedFormat('d F Y') }}
                                @if ($totalPayment != $item->total)
                                    @php
                                        $day = Carbon\Carbon::parse($item->due_date)->diffInDays();
                                    @endphp
                                    @if ($day <= 3 && $day >= 0)
                                        <span class="badge bg-warning">{{ $day == 0 ? 'Hari ini' : $day . ' hari lagi' }}</span>
                                    @endif
                                    @if ($day < 0 && $totalPayment < $item->total)
                                        <span class="badge bg-danger">{{ $day * -1 . ' hari lewat' }}</span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ Carbon\Carbon::parse($item->date)->translatedFormat('d F Y') }}</td>
                            <td>{{ Carbon\Carbon::parse($item->updated_at)->translatedFormat('d F Y') }}
                                <small class="text-muted">{{ Carbon\Carbon::parse($item->updated_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_show', [
                                        'title' => 'Pembelian',
                                        'href' => route('admin.purchases.show', $item->id),
                                        'permission' => 'purchases',
                                    ])
                                    @include('layouts.admin.partials.act_delete', [
                                        'title' => 'Pembelian',
                                        'href' => route('admin.purchases.destroy', $item->id),
                                        'permission' => 'purchases',
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
    </div>

@endsection
