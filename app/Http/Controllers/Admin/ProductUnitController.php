<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProductUnitController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Unit?', 'Apakah Anda yakin akan menghapus Unit ini?');

        $query = ProductUnit::query();

        $user = auth()->user();

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('symbol', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('store_id', $user->store_id);

        return self::view('admin.product-units.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.product-units.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'symbol' => 'required',
        ]);

        ProductUnit::create(array_merge($request->only('name', 'symbol'), [
            'store_id' => auth()->user()->store_id
        ]));

        Alert::success('Berhasil', 'Unit berhasil ditambahkan');

        return redirect()->route('admin.products.units.index');
    }

    public function edit(string $id)
    {
        $productUnit = ProductUnit::find($id);

        return self::view('admin.product-units.form', [
            'unit' => $productUnit
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'symbol' => 'required',
        ]);

        $productUnit = ProductUnit::find($id);

        $productUnit->update($request->only('name', 'symbol'));

        Alert::success('Berhasil', 'Unit berhasil diubah');

        return redirect()->route('admin.products.units.index');
    }

    public function destroy(string $id)
    {
        $productUnit = ProductUnit::find($id);

        if (ProductVariant::where('unit_id', $productUnit->id)->exists()) {
            Alert::error('Gagal', 'Unit tidak bisa dihapus karena masih digunakan');

            return redirect()->route('admin.products.units.index');
        }

        $productUnit->delete();

        Alert::success('Berhasil', 'Unit berhasil dihapus');

        return redirect()->route('admin.products.units.index');
    }
}
