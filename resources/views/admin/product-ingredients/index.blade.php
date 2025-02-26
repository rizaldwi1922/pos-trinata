@extends('layouts.admin.app')

@section('title', 'Daftar Produk Bahan')

@section('content')

    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Produk Bahan</h4>
                </div>
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
                        <th scope="col">Nama Produk</th>
                        <th scope="col">Stok</th>
                        <th scope="col">Barcode</th>
                        <th scope="col">Harga Modal</th>
                        <th scope="col">Harga Jual</th>
                        <th scope="col">Terakhir diperbaharui</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->name }}@if ($item->code)
                                    <br> <small class="text-muted">{{ $item->code }}</small>
                                @endif
                            </td>
                            <td>{{ App\Models\ProductIngredientStock::where('product_id', $item->id)
                                ->where(function ($query) {
                                    $query->whereNull('expired_at')
                                        ->orWhere('expired_at', '>', now());
                                })
                                ->sum('amount_available') }}</td>
                            <td><img style="max-width: 90px;"
                                    src="https://barcode.tec-it.com/barcode.ashx?data={{ $item->code }}&code=Code128&dpi=96"
                                    alt="barcode"></td>
                            <td>@currency($item->buy_price)</td>
                            <td>@currency($item->sell_price)</td>
                            <td>{{ Carbon\Carbon::parse($item->updated_at)->translatedFormat('d F Y') }}
                                <small class="text-muted">{{ Carbon\Carbon::parse($item->updated_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_edit', [
                                        'title' => 'Produk',
                                        'href' => route('admin.products.edit', $item->id),
                                        'permission' => 'product_ingredients',
                                    ])
                                    @include('layouts.admin.partials.act_delete', [
                                        'title' => 'Produk',
                                        'href' => route('admin.products.destroy', $item->id),
                                        'permission' => 'product_ingredients',
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
