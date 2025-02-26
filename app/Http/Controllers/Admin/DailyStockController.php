<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class DailyStockController extends Controller
{
    public function index(Request $request)
    {
        $filter_date =  $request->filter_date ?? date('Y-m-d');

        $query = DB::table('daily_stocks')->join('products as p', 'p.id', '=', 'daily_stocks.product_id')
            ->join('product_variants as pv', 'pv.id', '=', 'daily_stocks.variant_id')
            ->join('product_units as pu', 'pu.id', '=', 'pv.unit_id')
            ->select('daily_stocks.*', 'p.name as product_name', 'pv.measurement', 'pu.name as unit_name', 'pv.code');

        $query->where('daily_stocks.store_id', auth()->user()->store_id);

        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('p.name', env('DB_SEARCH_OPERATOR'), "%{$request->search}%")
                    ->orWhere('pv.code', env('DB_SEARCH_OPERATOR'), "%{$request->search}%");
            });
        }

        $query->whereDate('daily_stocks.date', $filter_date);

        $data = $query->paginate(10);

        return self::view('admin.daily-stocks.index', compact('data', 'filter_date'));
    }

    public function store(Request $request)
    {
        $filter_date = $request->filter_date ?? date('Y-m-d');

        $user = auth()->user();

        // delete all data in daily_stocks
        DB::table('daily_stocks')->where('store_id', $user->store_id)->where('date', $filter_date)->delete();

        // FIXME: only support for product variant
        $productIds = Product::where('buy_price', null)->pluck('id');

        foreach ($productIds as $productId) {
            $product = Product::find($productId);

            $variants = $product->variants;
            foreach ($variants as $variant) {
                $stock = ProductStock::where('variant_id', $variant->id)->where(function ($query) {
                    $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
                })->sum('amount_available');

                // insert to stock_dailys
                DB::table('daily_stocks')->insert([
                    'store_id' => $variant->store_id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'user_id' => $user->id,
                    'amount_start' => $stock,
                    'date' => $filter_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Alert::success('Success', 'Data Stok berhasil ditarik');

        return redirect()->route('admin.daily-stocks.index', ['filter_date' => $filter_date]);
    }

    public function edit(string $id)
    {
        $data = DB::table('daily_stocks')->where('id', $id)->first();

        $product = Product::find($data->product_id);
        $variant = $product->variants->where('id', $data->variant_id)->first();
        $unit = $variant->unit;

        $data->name = $product->name . ' / ' . $variant->measurement . ' ' . $unit->name;

        return self::view('admin.daily-stocks.form', compact('data'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'amount_end' => 'required|numeric|min:0',
        ]);

        $data = DB::table('daily_stocks')->where('id', $id)->first();

        DB::table('daily_stocks')->where('id', $id)->update([
            'amount_end' => $request->amount_end,
        ]);

        Alert::success('Success', 'Data Stok berhasil diupdate');

        return redirect()->route('admin.daily-stocks.index', ['filter_date' => $data->date]);
    }
}
