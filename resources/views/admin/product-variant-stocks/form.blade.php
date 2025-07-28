@extends('layouts.admin.app')

@section('title', 'Tambah Stok Produk')

@section('content')

    <div class="card mt-4">
         <div class="card-header">
            <a href="/admin/product/variant-stocks" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                </svg>
                Kembali</a>
        </div>
        <form action="{{ route('admin.products.variant-stocks.store') }}" class="form-control" method="POST">
            @csrf
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Tambah Stok Produk Varian</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="variant_id" class="form-label mb-0">Produk Varian <span class="text-danger">*</span></label>
                    <select class="form-select select2" id="variant_id" name="variant_id" required>
                        @php
                            $variants = \App\Models\ProductVariant::select('product_variants.*')
                                ->join('products', 'product_variants.product_id', '=', 'products.id')
                                ->where('product_variants.store_id', auth()->user()->store_id)
                                ->orderBy('products.name')
                                ->get();
                        @endphp
                        <option value="">Pilih Produk Varian</option>
                        @foreach ($variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->product->name }} - {{ $variant->measurement }}
                                {{ $variant->unit->symbol }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="supplier_id" class="form-label
                        mb-0">Supplier </label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">Pilih Supplier</option>
                        @foreach (App\Models\Supplier::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get() as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">Kode</label>
                    <input type="text" class="form-control" id="code" name="code">
                </div>

                <div class="mb-3">
                    <label for="amount_added" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="amount_added" name="amount_added" required>
                </div>

                <div class="mb-3">
                    <label for="expiry_date" class="form-label
                        mb-0">Kadaluwarsa Pada</label>
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-default">Simpan</button>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endpush
