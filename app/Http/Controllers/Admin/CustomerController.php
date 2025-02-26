<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Pelanggan?', 'Apakah Anda yakin akan menghapus Pelanggan ini?');

        $query = Customer::query();

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('email', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('phone', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $user = auth()->user();

        $query->where('store_id', $user->store_id);

        return self::view('admin.customers.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.customers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable',
        ]);

        $data = $request->only('name', 'email', 'phone');

        $data['store_id'] = auth()->user()->store_id;

        Customer::create($data);

        Alert::success('Berhasil', 'Pelanggan berhasil ditambahkan');

        return redirect()->route('admin.customers.index');
    }

    public function edit(string $id)
    {
        $customer = Customer::find($id);

        return self::view('admin.customers.form', [
            'customer' => $customer
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'phone' => 'nullable',
        ]);

        $customer = Customer::find($id);

        $customer->update($request->only('name', 'email', 'phone'));

        Alert::success('Berhasil', 'Pelanggan berhasil diubah');

        return redirect()->route('admin.customers.index');
    }

    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (Transaction::where('customer_id', $id)->exists()) {
            Alert::error('Gagal', 'Pelanggan tidak bisa dihapus karena memiliki data transaksi');

            return redirect()->route('admin.customers.index');
        }

        $customer->delete();

        Alert::success('Berhasil', 'Pelanggan berhasil dihapus');

        return redirect()->route('admin.customers.index');
    }
}
