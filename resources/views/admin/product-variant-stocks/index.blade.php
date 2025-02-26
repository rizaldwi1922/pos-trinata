@extends('layouts.admin.app')

@section('title', 'Daftar Stok')

@section('content')

    <div class="card mt-4">
        <div class="table-responsive text-nowrap">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="card-title
                            d-flex align-items-center">Daftar Stok Produk Varian</h4>
                    </div>
                    @if ($blade_user->hasPermissionTo('can_create_admin_product_variant_stocks'))
                        <div class="col-md-6 text-end">
                            <a href="{{ route('admin.products.variant-stocks.create') }}" class="btn btn-default"><i class="bx bx-plus"></i> &nbsp;Tambah Stok</a>
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
            <table class="table table-hover">
                <thead>
                    <tr>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Invoice ID</th>
                        <th scope="col">Supplier</th>
                        <th scope="col">Produk</th>
                        <th scope="col">Varian</th>
                        <th scope="col">Jumlah / Tersedia</th>
                        <th scope="col">Kadaluwarsa pada</th>
                        <th scope="col">Ditambahkan pada</th>
                        <th scope="col"></th>
                    </tr>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td class="no">
                                {{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}
                            </td>
                            @php
                                $purchase = $item->purchase;
                            @endphp
                            <td>
                                <a href="{{ $purchase ? route('admin.purchases.show', $purchase->id) : 'javascript::void(0)' }}">{{ $purchase ? $purchase->invoice_number : '-' }}</a>
                            </td>
                            <td>
                                {{ $item->supplier->name ?? '-' }}</td>
                            <td>
                                {{ $item->product->name }} @if($item->code) <br> <small class="text-muted">{{ $item->code }}</small>@endif</td>
                            <td>{{ $item->variant->measurement . ' / ' . $item->variant->unit->symbol }}</td>
                            <td>{{ $item->amount_added . ' / ' . $item->amount_available }} @if ($item->amount_available == 0)
                                    <small><small><small><span class="badge bg-warning">Habis</span></small></small></small>
                                @endif
                                @if ($item->expired_at && Carbon\Carbon::parse($item->expired_at)->isPast() && $item->amount_available > 0)
                                    <br><small><small><small><span class="badge bg-danger">Kadaluwarsa</span></small></small></small>
                                @endif
                            </td>
                            <td>{{ $item->expired_at ? Carbon\Carbon::parse($item->expired_at)->translatedFormat('d F Y') : '-' }}
                            </td>
                            <td>
                                {{ Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }} <small
                                    class="text-muted">{{ Carbon\Carbon::parse($item->created_at)->format('H:i') }}
                                    WIB <br>oleh {{ $item->user->name }}</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_delete', [
                                        'title' => 'Stok',
                                        'href' => route('admin.products.variant-stocks.destroy', $item->id),
                                        'permission' => 'product_variant_stocks',
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
