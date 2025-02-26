@extends('layouts.admin.app')

@section('title', 'Tambah Stok Produk')

@section('content')

    <div class="card mt-4">
        <form action="{{ route('admin.ingredients-stocks.store') }}" class="form-control" method="POST">
            @csrf
            <div class="card-header">
                <h4 class="card-title
                    d-flex align-items-center">Tambah Stok Produk</h4>
            </div>
            <div class="card-body">
                <div class="mb-3" id="form_ingredient">
                    <label for="ingredient_id" class="form-label mb-0">Bahan <span class="text-danger">*</span></label>
                    <select class="form-select" id="ingredient_id" name="ingredient_id" required>
                        <option value="">Pilih Bahan</option>
                        @foreach (App\Models\ProductIngredient::where('store_id', auth()->user()->store_id)->get() as $product_ingredient)
                            <option value="{{ $product_ingredient->id }}">{{ $product_ingredient->name }}
                                ({{ $product_ingredient->unit->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col-6">
                            <label for="supplier_id" class="form-label">Supplier </label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">Pilih Supplier</option>
                                @foreach (App\Models\Supplier::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get() as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6">
                            <label for="code" class="form-label">Kode (Otomatis jika dikosongkan)</label>
                            <input type="text" class="form-control" id="code" placeholder="ex. STK-001" name="code">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="amount_added" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" placeholder="ex. 10" id="amount_added" name="amount_added" required>
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
