<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Store;
use App\Models\Setting;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Transaction::query();

        $query->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id');
        $query->leftJoin('customers', 'transactions.customer_id', '=', 'customers.id');
        $query->where('transactions.store_id', $user->store_id);
        $query->orderBy('transactions.created_at', 'desc');

        if ($request->search) {
            $op = env('DB_SEARCH_OPERATOR', 'LIKE');
            $query->where(function ($q) use ($request, $op) {
                $q->where('trx_id', $op, "%{$request->search}%")
                    ->orWhere('customers.name', $op, "%{$request->search}%")
                    ->orWhere('payment_methods.name', $op, "%{$request->search}%");
            });
        }

        // Filter rentang tanggal
        if ($request->from) {
            $query->where(
                'transactions.created_at',
                '>=',
                \Carbon\Carbon::parse($request->from)->startOfDay()
            );
        }
        if ($request->to) {
            $query->where(
                'transactions.created_at',
                '<=',
                \Carbon\Carbon::parse($request->to)->endOfDay()
            );
        }

        $query->select(
            'transactions.*',
            'payment_methods.name as payment_method_name',
            'payment_methods.is_cash',
            'customers.name as customer_name',
        );

        $perPage = $request->per_page ?? 15;
        $data    = $query->latest('transactions.created_at')->paginate($perPage);

        return response()->json([
            'data'         => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
            'per_page'     => $data->perPage(),
            'total'        => $data->total(),
            'has_more'     => $data->hasMorePages(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $user        = $request->user();
        $transaction = Transaction::findOrFail($id);

        // Pastikan hanya store yang sama yang bisa akses
        if ($transaction->store_id !== $user->store_id) {
            return response()->json(['message' => 'Tidak ditemukan'], 404);
        }

        $store    = Store::find($transaction->store_id);
        $settings = Setting::whereIn('key', ['receipt_logo', 'receipt_logo_image', 'receipt_logo_size'])
            ->where('store_id', $transaction->store_id)
            ->pluck('value', 'key')
            ->toArray();

        return response()->json([
            'transaction'       => $transaction,
            'store'             => $store,
            'settings'          => $settings,
            'payment_method'    => $transaction->paymentMethod,
            'customer'          => $transaction->customer,
        ]);
    }
}
