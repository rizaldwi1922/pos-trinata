<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Supplier?', 'Apakah Anda yakin akan menghapus Supplier ini?');

        $query = Supplier::query();

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('email', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('phone', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $user = auth()->user();

        $query->where('store_id', $user->store_id);

        return self::view('admin.suppliers.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.suppliers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:suppliers,email',
            'phone' => 'required',
            'address' => 'required',
        ]);

        $data = $request->only('name', 'email', 'phone', 'address');

        $data['store_id'] = auth()->user()->store_id;

        Supplier::create($data);

        Alert::success('Berhasil', 'Supplier berhasil ditambahkan');

        return redirect()->route('admin.suppliers.index');
    }

    public function edit(string $id)
    {
        $supplier = Supplier::find($id);

        return self::view('admin.suppliers.form', [
            'supplier' => $supplier
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:suppliers,email,' . $id,
            'phone' => 'required',
            'address' => 'required',
        ]);

        $supplier = Supplier::find($id);

        $supplier->update($request->only('name', 'email', 'phone', 'address'));

        Alert::success('Berhasil', 'Supplier berhasil diubah');

        return redirect()->route('admin.suppliers.index');
    }

    public function destroy(Supplier $supplier)
    {
        if (ProductStock::where('supplier_id', $supplier->id)->exists()) {
            Alert::error('Gagal', 'Supplier tidak bisa dihapus karena sudah tereferensi oleh produk');

            return redirect()->route('admin.suppliers.index');
        }

        $supplier->delete();

        Alert::success('Berhasil', 'Supplier berhasil dihapus');

        return redirect()->route('admin.suppliers.index');
    }
}
