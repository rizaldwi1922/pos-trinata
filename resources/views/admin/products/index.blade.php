@extends('layouts.admin.app')

@section('title', 'Daftar Produk')

@section('content')
    <div class="card mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="card-title
                        d-flex align-items-center">Daftar Produk</h4>
                </div>
                <div class="col-md-4 text-end">
                    <div style="display:flex; gap: 5px;">
                        @if ($blade_user->hasPermissionTo('can_view_admin_product_barcodes'))
                            <a onclick="printBarcode()" href="javascript::void(0)" class="col-6 btn btn-default"
                                style="background-color: orange;">
                                <i class="bx bx-barcode"></i> &nbsp;Cetak Barcode
                            </a>
                        @else
                            <div class="col-6"></div>
                        @endif
                        @if ($blade_user->hasPermissionTo('can_create_admin_products'))
                            <a href="{{ route('admin.products.create') }}" class="col-6 btn btn-default"><i
                                    class="bx bx-plus"></i> &nbsp;Tambah Produk</a>
                        @endif
                    </div>
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
                        <th scope="col">
                            <div class="form-check form-switch">
                                <input class="form-check
                                    form-check-switch"
                                    type="checkbox" id="select_all">
                            </div>
                        </th>
                        <th scope="col">No</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Tipe Produk</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Harga Modal / Jual</th>
                        <th scope="col">Terakhir diperbaharui</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($data as $item)
                        <tr>
                            <td>
                                <div class="form-check
                                    form-check-switch">
                                    <input class="form-check
                                        form-check-switch"
                                        type="checkbox" id="select_all"
                                        data-value="{{ $item->id }}">
                                </div>
                            </td>
                            <td class="no">{{ ($data->currentpage() - 1) * $data->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name }}</td>
                            <td>
                                @if ($item->buy_price)
                                    <span class="badge bg-primary">Bahan</span>
                                @else
                                    <span class="badge bg-info">Varian</span>
                                @endif
                            </td>
                            @php
                                $is_url = filter_var($item->image, FILTER_VALIDATE_URL);
                            @endphp
                            <td>
                                @if ($item->image)
                                    @if ($is_url)
                                        <img src="{{ $item->image }}" alt="{{ $item->name }}" class="img-fluid"
                                            onerror="this.src='{{ asset('assets/app/pos/images/product-default.png') }}'"
                                            width="50px" onclick="showImage(this)">
                                    @else
                                        <img src="@viewfile($item->image)" alt="{{ $item->name }}" class="img-fluid"
                                            onerror="this.src='{{ asset('assets/app/pos/images/product-default.png') }}'"
                                            width="50px" onclick="showImage(this)">
                                    @endif
                                @else
                                    <img src="{{ asset('assets/app/pos/images/product-default.png') }}"
                                        alt="{{ $item->name }}" class="img-fluid" width="50px">
                                @endif
                            </td>
                            <td>
                                @if ($item->buy_price)
                                    @currency($item->buy_price) / @currency($item->sell_price)
                                @else
                                    @php
                                        // Get the first variant with the lowest buy price
                                        $lowest_variant = App\Models\ProductVariant::where('product_id', $item->id)
                                            ->orderBy('buy_price', 'ASC')
                                            ->first();

                                        // Get the first variant with the highest buy price
                                        $highest_variant = App\Models\ProductVariant::where('product_id', $item->id)
                                            ->orderBy('buy_price', 'DESC')
                                            ->first();
                                    @endphp
                                    @if (!empty($lowest_variant) && !empty($highest_variant))
                                        @if ($lowest_variant->id != $highest_variant->id)
                                            <i class='bx bx-chevron-down'></i>
                                        @endif
                                        @currency($lowest_variant->buy_price) / @currency($lowest_variant->sell_price)
                                        @if ($lowest_variant->id != $highest_variant->id)
                                            <br>
                                            <span class="text-info"><i class='bx bx-chevrons-up'></i>
                                                @currency($highest_variant->buy_price) /
                                                @currency($highest_variant->sell_price)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                @endif
                            </td>
                            <td>{{ Carbon\Carbon::parse($item->updated_at)->translatedFormat('d F Y') }}
                                <small class="text-muted">{{ Carbon\Carbon::parse($item->updated_at)->format('H:i') }}
                                    WIB</small>
                            </td>
                            <td>
                                <ul class="list-inline hstack gap-2 mb-0">
                                    @include('layouts.admin.partials.act_edit', [
                                        'title' => 'Produk',
                                        'href' => route('admin.products.edit', $item->id),
                                        'permission' => 'products',
                                    ])
                                    @include('layouts.admin.partials.act_delete', [
                                        'title' => 'Produk',
                                        'href' => route('admin.products.destroy', $item->id),
                                        'permission' => 'products',
                                    ])
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <script>
                document.getElementById('select_all').addEventListener('change', function() {
                    var checkboxes = document.querySelectorAll('.form-check-switch');
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });
                });

                function printBarcode() {
                    var checkboxes = document.querySelectorAll('.form-check-switch');
                    var selected = [];
                    checkboxes.forEach((checkbox) => {
                        if (checkbox.checked) {
                            selected.push(checkbox.getAttribute('data-value'));
                        }
                    });

                    selected = selected.filter(function(el) {
                        return el != null;
                    });

                    if (selected.length > 0) {
                        var url = '{{ route('admin.products.barcode', 'ids=:ids') }}';
                        url = url.replace(':ids', selected.join(','));
                        window.open(url, '_blank');
                    } else {
                        Swal.fire({
                            title: 'Cetak Barcode',
                            text: 'Apakah Anda yakin ingin mencetak semua barcode produk?',
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Cetak',
                            cancelButtonText: 'Batal',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open('{{ route('admin.products.barcode') }}', '_blank');
                            }
                        });
                    }
                }

                function showImage(element) {
                    Swal.fire({
                        imageUrl: element.src,
                        imageWidth: 500,
                        imageHeight: 500,
                        imageAlt: element.alt,
                    });
                }
            </script>
        </div>
        <div class="footer">
            @include('layouts.admin.partials.pagination', ['data' => $data])
        </div>
    </div>

@endsection
