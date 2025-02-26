@extends('layouts.admin.app')

@section('title', 'Tambah Stok Produk')

@section('content')

    <div class="card mt-4">
        <form action="{{ route('admin.products.ingredient-stocks.store') }}" class="form-control" method="POST">
            @csrf
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Tambah Stok Produk</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="product_id" class="form-label mb-0">Produk Bahan <span class="text-danger">*</span></label>
                    <select class="form-select" id="product_id" name="product_id">
                        <option value="">Pilih Produk</option>
                        @foreach (App\Models\Product::where('store_id', auth()->user()->store_id)->whereNotNull('sell_price')->get() as $productFill)
                            <option value="{{ $productFill->id }}">{{ $productFill->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col-6">
                            <label for="amount_added" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" placeholder="ex. 10" id="amount_added" name="amount_added" required>
                        </div>

                        <div class="col-6">
                            <label for="code" class="form-label">Kode (Otomatis jika dikosongkan)</label>
                            <input type="text" class="form-control" id="code" placeholder="ex. STK-001" name="code">
                        </div>
                    </div>
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
