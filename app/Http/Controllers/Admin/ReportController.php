<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PurchaseExport;
use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class ReportController extends Controller
{
    public function indexTransaction()
    {
        return self::view('admin.reports.transactions.index');
    }

    public function showTransaction(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

        if (empty($start_date) || empty($end_date)) {
            Alert::error('Gagal', 'Filter tanggal tidak valid');

            return redirect()->route('admin.report.transactions.index');
        }

        $data = Transaction::whereBetween('created_at', [$start_date, $end_date])
            ->where('store_id', auth()->user()->store_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($trx) {
                $res = array();
                $res['customer'] = $trx->customer->name ?? '';
                $res['amount_total'] = $trx->amount_total - $trx->amount_retur;
                $res['amount_profit'] = $trx->amount_profit;
                $res['total_items'] = $trx->total_items;
                $res['payment_method'] = $trx->paymentMethod->name;
                $res['data'] = $trx->data;
                $res['created_at'] = $trx->created_at->translatedFormat('d F Y');
                return $res;
            });

        $expenses = Expense::whereBetween('date', [$start_date, $end_date])
            ->where('store_id', auth()->user()->store_id)
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($expense) {
                $res = array();
                $res['name'] = $expense->name;
                $res['amount'] = $expense->amount;
                $res['date'] = Carbon::parse($expense->date)->translatedFormat('d F Y');
                return $res;
            });

        $end_date = $request->end_date;

        return self::view('admin.reports.transactions.show', compact('data', 'expenses', 'start_date', 'end_date'));
    }

    public function indexPurchase()
    {
        return self::view('admin.reports.purchases.index');
    }

    public function showPurchase(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

        if (empty($start_date) || empty($end_date)) {
            Alert::error('Gagal', 'Filter tanggal tidak valid');

            return redirect()->route('admin.report.purchases.index');
        }

        $data = Purchase::whereBetween('created_at', [$start_date, $end_date])
            ->where('store_id', auth()->user()->store_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($purchase) {
                $res = array();
                $res['invoice_number'] = $purchase->invoice_number;
                $res['due_date'] = Carbon::parse($purchase->due_date)->translatedFormat('d F Y');
                $res['amount_total'] = $purchase->total;
                $res['created_at'] = $purchase->created_at->translatedFormat('d F Y');

                $dataFirst = DB::table('product_stocks')->where('purchase_id', $purchase->id)
                    ->select(DB::raw("CONCAT('v,', product_stocks.id) as id"), 'product_stocks.code', 'suppliers.name as supplier_name', 'products.name as product_name', 'product_variants.measurement as variant_measurement', 'product_units.name as unit_name', 'product_stocks.amount_added', 'product_stocks.amount_available', 'product_stocks.expired_at')
                    ->join('suppliers', 'product_stocks.supplier_id', '=', 'suppliers.id')
                    ->join('products', 'product_stocks.product_id', '=', 'products.id')
                    ->join('product_variants', 'product_stocks.variant_id', '=', 'product_variants.id')
                    ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
                    ->get();

                $dataSecond = DB::table('ingredient_stocks')->where('purchase_id', $purchase->id)
                    ->select(DB::raw("CONCAT('i,', ingredient_stocks.id) as id"), 'ingredient_stocks.code', 'suppliers.name as supplier_name', 'product_ingredients.name as ingredient_name', 'product_units.name as unit_name', 'ingredient_stocks.amount_added', 'ingredient_stocks.amount_available', 'ingredient_stocks.expired_at')
                    ->join('suppliers', 'ingredient_stocks.supplier_id', '=', 'suppliers.id')
                    ->join('product_ingredients', 'ingredient_stocks.ingredient_id', '=', 'product_ingredients.id')
                    ->join('product_units', 'product_ingredients.unit_id', '=', 'product_units.id')
                    ->get();

                $purchaseDetails = $dataFirst->merge($dataSecond);

                $res['purchase_details'] = $purchaseDetails->toArray();

                return $res;
            });

        $end_date = $request->end_date;

        return self::view('admin.reports.purchases.show', compact('data', 'start_date', 'end_date'));
    }

    public function exportTransaction(Request $request)
    {
        return Excel::download(new TransactionExport(
            $request->start_date,
            $request->end_date
        ), 'laporan-penjualan-' . now()->format('YmdHis') . '.xlsx');
    }

    public function exportPurchase(Request $request)
    {
        return Excel::download(new PurchaseExport(
            $request->start_date,
            $request->end_date
        ), 'laporan-pembelian-' . now()->format('YmdHis') . '.xlsx');
    }
}
