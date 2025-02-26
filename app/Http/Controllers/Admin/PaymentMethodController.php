<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Metode Pembayaran?', 'Apakah Anda yakin akan menghapus Metode Pembayaran ini?');

        $query = PaymentMethod::query();

        if ($request->search) {
            $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
        }

        $user = auth()->user();

        $query->where('store_id', $user->store_id);

        return self::view('admin.payment-methods.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.payment-methods.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
        ]);

        $user = Auth::user();

        $data = $request->only('name', 'code');
        $data['is_cash'] = $request->has('is_cash') && $request->is_cash;
        $data['store_id'] = $user->store_id;

        PaymentMethod::create($data);

        Alert::success('Berhasil', 'Metode Pembayaran berhasil ditambahkan');

        return redirect()->route('admin.payment-methods.index');
    }

    public function edit(string $id)
    {
        $paymentMethod = PaymentMethod::find($id);

        return self::view('admin.payment-methods.form', [
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
        ]);

        $paymentMethod = PaymentMethod::find($id);

        $data = $request->only('name', 'code');
        $data['is_cash'] = $request->has('is_cash') && $request->is_cash;

        $paymentMethod->update($data);

        Alert::success('Berhasil', 'Metode Pembayaran berhasil diubah');

        return redirect()->route('admin.payment-methods.index');
    }

    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::find($id);

        if (Transaction::where('payment_method_id', $paymentMethod->id)->exists()) {
            Alert::error('Gagal', 'Metode Pembayaran tidak bisa dihapus karena sudah digunakan');

            return redirect()->route('admin.payment-methods.index');
        }

        $paymentMethod->delete();

        Alert::success('Berhasil', 'Metode Pembayaran berhasil dihapus');

        return redirect()->route('admin.payment-methods.index');
    }
}
