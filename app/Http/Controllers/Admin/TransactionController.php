<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Transaksi?', 'Apakah Anda yakin akan menghapus Transaksi ini?');

        $query = Transaction::query();

        $user = auth()->user();

        $query->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id');

        $query->leftJoin('customers', 'transactions.customer_id', '=', 'customers.id');

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('trx_id', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('customers.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('payment_methods.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('transactions.store_id', $user->store_id);

        $query->select('transactions.*');

        return self::view('admin.transactions.index', [
            'data' => $query->latest()->paginate(10)
        ]);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if (Auth::user()) {
            $is_access = true;
        } else {
            try {
                $temp_key = decrypt($request->temp_key);
                if ($temp_key == $transaction->store_id) {
                    $is_access = true;
                }
            } catch (\Exception $e) {
                $is_access = false;
            }
        }

        if (!$is_access) {
            return abort(404);
        }

        $store_id = Auth::user() ? Auth::user()->store_id : $temp_key;

        $payload = [
            'store' => Store::find($store_id),
            'transaction' => $transaction,
            'settings' => Setting::whereIn('key', ['receipt_logo', 'receipt_logo_image', 'receipt_logo_size'])->where('store_id', $store_id)->pluck('value', 'key')->toArray(),
        ];

        $payload['from'] = $request->from ?? 'pos';

        $payload['btn_method'] = $request->btn_method ?? 'default';

        return view('admin.transactions.print', $payload);
    }

    public function destroy(Transaction $transaction)
    {
        DB::table('transaction_logs')->where('transaction_id', $transaction->id)->delete();

        $transaction->delete();

        Alert::success('Berhasil', 'Transaksi berhasil dihapus');

        return redirect()->route('admin.transactions.index');
    }

    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required'
        ]);

        $ids = explode(',', $request->ids);

        DB::table('transaction_logs')->whereIn('transaction_id', $ids)->delete();

        Transaction::whereIn('id', $ids)->delete();

        Alert::success('Berhasil', 'Transaksi berhasil dihapus');

        return redirect()->route('admin.transactions.index');
    }
}
