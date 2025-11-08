<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class DashboardController extends Controller
{
    // public function index(Request $request)
    // {
    //     $validFilters = ['Harian', 'Bulanan'];

    //     $filter_transaction = $this->handleFilterSession($request, 'filter_transaction', 'Harian', $validFilters);
    //     $filter_graph_transaction = $this->handleFilterSession($request, 'filter_graph_transaction', 'Harian', $validFilters);
    //     $filter_today_transaction = $this->handleFilterSession($request, 'filter_today_transaction', 'Harian', $validFilters);
    //     $filter_profit = $this->handleFilterSession($request, 'filter_profit', 'Harian', $validFilters);

    //     $user = Auth::user();

    //     $s_total_transaction = Transaction::where('store_id', $user->store_id)
    //         ->when($filter_transaction == 'Harian', function ($query) {
    //             return $query->whereDate('created_at', Carbon::today());
    //         })
    //         ->when($filter_transaction == 'Bulanan', function ($query) {
    //             return $query->whereDate('created_at', '>=', Carbon::today()->subDays(30));
    //         })
    //         ->sum('amount_total');
    //     $s_total_profit = Transaction::where('store_id', $user->store_id)
    //         ->when($filter_profit == 'Harian', function ($query) {
    //             return $query->whereDate('created_at', Carbon::today());
    //         })
    //         ->when($filter_transaction == 'Bulanan', function ($query) {
    //             return $query->whereDate('created_at', '>=', Carbon::today()->subDays(30));
    //         })
    //         ->sum('amount_profit');
    //     $s_amount_transaction = Transaction::where('store_id', $user->store_id)
    //         ->when($filter_today_transaction == 'Harian', function ($query) {
    //             return $query->whereDate('created_at', Carbon::today());
    //         })
    //         ->when($filter_today_transaction == 'Bulanan', function ($query) {
    //             return $query->whereDate('created_at', '>=', Carbon::today()->subDays(30));
    //         })
    //         ->count();
    //     $s_amount_transaction_all = Transaction::where('store_id', $user->store_id)->count();

    //     $arr_transactions = DB::table('transaction_logs')
    //         ->where('store_id', $user->store_id)
    //         ->where('product_id', '!=', null) // filter for product only
    //         ->where('amount_before', '!=', null)
    //         ->where('amount_after', '!=', null)
    //         ->groupBy('product_id', 'ingredient_id', 'variant_id')
    //         ->selectRaw('product_id, ingredient_id, variant_id, sum(amount) as total')
    //         ->orderBy('total', 'desc')
    //         ->limit(5)
    //         ->get()->map(function ($item) {
    //             $product = Product::find($item->product_id);
    //             $variant = ProductVariant::find($item->variant_id);
    //             $category = ProductCategory::find($product->category_id);

    //             return [
    //                 'product_id' => $product->id,
    //                 'product_name' => $product->name . ($variant ? (' (' . $variant->measurement . ' ' . $variant->unit->name . ')') : ''),
    //                 'product_image' => $product->image,
    //                 'category_id' => $category->id,
    //                 'category_name' => $category->name,
    //                 'total' => $item->total,
    //             ];
    //         });

    //     $arr_last_transactions = Transaction::where('store_id', $user->store_id)
    //         ->orderBy('id', 'desc')
    //         ->limit(6)
    //         ->get()->toArray();

    //     $limit_graph = $filter_graph_transaction == 'Harian' ? 7 : 30;
    //     $day_mins = range(0, $limit_graph - 1);
    //     $arr_chart_data = [];
    //     foreach ($day_mins as $day_min) {
    //         $date = Carbon::now()->subDays($day_min)->format('Y-m-d');
    //         $total = Transaction::where('store_id', $user->store_id)
    //             ->whereDate('created_at', $date)
    //             ->sum('amount_total');

    //         $arr_chart_data[] = [
    //             'date' => Carbon::parse($date)->translatedFormat('d F'),
    //             'total' => (int) $total,
    //         ];
    //     }

    //     $product_stocks = DB::table('product_variants')
    //         ->join('product_stocks', 'product_variants.id', '=', 'product_stocks.variant_id')
    //         ->join('products', 'product_variants.product_id', '=', 'products.id')
    //         ->select('product_variants.id as variant_id', DB::raw('NULL as ingredient_id'), 'products.name as name', 'product_variants.code as code', DB::raw('SUM(product_stocks.amount_available) as total_stock'), DB::raw("'product' as type"))
    //         ->where('product_variants.store_id', $user->store_id)
    //         ->where(function ($query) {
    //             $query->whereNull('product_stocks.expired_at')
    //                 ->orWhere('product_stocks.expired_at', '>', Carbon::now());
    //         })
    //         ->groupBy('product_variants.id', 'products.name', 'product_variants.code');

    //     $ingredient_stocks = DB::table('product_ingredients')
    //         ->join('ingredient_stocks', 'product_ingredients.id', '=', 'ingredient_stocks.ingredient_id')
    //         ->select(DB::raw('NULL as variant_id'), 'product_ingredients.id as ingredient_id', 'product_ingredients.name as name', 'product_ingredients.code as code', DB::raw('SUM(ingredient_stocks.amount_available) as total_stock'), DB::raw("'ingredient' as type"))
    //         ->where('product_ingredients.store_id', $user->store_id)
    //         ->where(function ($query) {
    //             $query->whereNull('ingredient_stocks.expired_at')
    //                 ->orWhere('ingredient_stocks.expired_at', '>', Carbon::now());
    //         })
    //         ->groupBy('product_ingredients.id', 'product_ingredients.name', 'product_ingredients.code');

    //     $low_stock_items = $product_stocks
    //         ->union($ingredient_stocks)
    //         ->orderBy('total_stock', 'asc')
    //         ->limit(10)
    //         ->paginate(5);

    //     $payload = [
    //         's_total_transaction' => $s_total_transaction,
    //         's_total_profit' => $s_total_profit,
    //         's_amount_transaction' => $s_amount_transaction,
    //         's_amount_transaction_all' => $s_amount_transaction_all,
    //         'arr_chart_data' => $arr_chart_data,
    //         'arr_transactions' => $arr_transactions,
    //         'arr_last_transactions' => $arr_last_transactions,
    //         'low_stock_items' => $low_stock_items,
    //     ];

    //     return self::view('admin.dashboard.index', compact('payload'));
    // }

    public function handleFilterSession($request, $sessionKey, $default, $validFilters)
    {
        $filter = session($sessionKey, $default);
        if ($request->$sessionKey) {
            if (!in_array($request->$sessionKey, $validFilters)) {
                Alert::error('Error', 'Filter transaksi tidak valid');
                return redirect()->back();
            }
            session([$sessionKey => $request->$sessionKey]);
            $filter = $request->$sessionKey;
        }
        return $filter;
    }

    public function index(Request $request)
    {
        return self::view('admin.dashboard.index');
    }

    public function salesChart(Request $request)
    {
        $mode = $request->query('mode', 'daily');
        $offset = (int) $request->query('offset', 0);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $periodeFormat = match ($mode) {
            'daily' => "DATE(jd.created_at)",
            'weekly' => "DATE_FORMAT(jd.created_at, '%x-W%v')",
            'monthly' => "DATE_FORMAT(jd.created_at, '%Y-%m')",
            default => "YEAR(jd.created_at)",
        };

        $query = "
        SELECT
            {$periodeFormat} AS periode,
            SUM(CASE WHEN jd.account_code IN ('4010','4020') THEN jd.credit - jd.debit ELSE 0 END) AS total_penjualan,
            SUM(CASE WHEN jd.account_code IN ('5010','5020') THEN jd.debit - jd.credit ELSE 0 END) AS total_hpp,
            (
                SUM(CASE WHEN jd.account_code IN ('4010','4020') THEN jd.credit - jd.debit ELSE 0 END)
                - SUM(CASE WHEN jd.account_code IN ('5010','5020') THEN jd.debit - jd.credit ELSE 0 END)
            ) AS total_laba
        FROM journal_details jd
        WHERE jd.account_code IN ('4010','4020','5010','5020')
    ";

        // 🔹 Jika user memilih tanggal manual, abaikan offset dan mode default
        $bindings = [];
        if ($startDate && $endDate) {
            $query .= " AND DATE(jd.created_at) BETWEEN ? AND ?";
            $bindings = [$startDate, $endDate];
        } elseif ($mode === 'daily') {
            $query .= "
            AND DATE(jd.created_at) BETWEEN 
                DATE_SUB(CURDATE(), INTERVAL ((? + 1) * 7) DAY)
                AND DATE_SUB(CURDATE(), INTERVAL (? * 7) DAY)
        ";
            $bindings = [$offset, $offset];
        } elseif ($mode === 'weekly') {
            $query .= "
            AND YEARWEEK(jd.created_at, 1) BETWEEN 
                YEARWEEK(DATE_SUB(CURDATE(), INTERVAL ((? + 1) * 8) WEEK), 1)
                AND YEARWEEK(DATE_SUB(CURDATE(), INTERVAL (? * 8) WEEK), 1)
        ";
            $bindings = [$offset, $offset];
        } elseif ($mode === 'monthly') {
            $query .= " AND YEAR(jd.created_at) = (YEAR(CURDATE()) - ?)";
            $bindings = [$offset];
        }

        $query .= " GROUP BY {$periodeFormat} ORDER BY {$periodeFormat}";

        $data = DB::select($query, $bindings);

        return response()->json($data);
    }

    public function netIncomeChart(Request $request)
    {
        $mode = $request->query('mode', 'daily');
        $offset = (int) $request->query('offset', 0);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $periodeFormat = match ($mode) {
            'daily' => "DATE(jd.created_at)",
            'weekly' => "DATE_FORMAT(jd.created_at, '%x-W%v')",
            'monthly' => "DATE_FORMAT(jd.created_at, '%Y-%m')",
            default => "YEAR(jd.created_at)",
        };

        $query = "
        SELECT
            {$periodeFormat} AS periode,
            -- Laba kotor: Penjualan - HPP
            (
                SUM(CASE WHEN jd.account_code IN ('4010','4020') THEN jd.credit - jd.debit ELSE 0 END)
                - SUM(CASE WHEN jd.account_code IN ('5010','5020') THEN jd.debit - jd.credit ELSE 0 END)
            ) AS laba_kotor,

            -- Total pengeluaran (semua akun 5xxx selain HPP)
            SUM(CASE WHEN jd.account_code BETWEEN 5030 AND 5999 THEN jd.debit - jd.credit ELSE 0 END) AS pengeluaran,

            -- Laba bersih (laba kotor - pengeluaran)
            (
                (
                    SUM(CASE WHEN jd.account_code IN ('4010','4020') THEN jd.credit - jd.debit ELSE 0 END)
                    - SUM(CASE WHEN jd.account_code IN ('5010','5020') THEN jd.debit - jd.credit ELSE 0 END)
                )
                - SUM(CASE WHEN jd.account_code BETWEEN 5030 AND 5999 THEN jd.debit - jd.credit ELSE 0 END)
            ) AS laba_bersih
        FROM journal_details jd
        WHERE jd.account_code BETWEEN 4000 AND 5999
    ";

        // Filter tanggal manual (jika dipilih)
        $bindings = [];
        if ($startDate && $endDate) {
            $query .= " AND DATE(jd.created_at) BETWEEN ? AND ?";
            $bindings = [$startDate, $endDate];
        } elseif ($mode === 'daily') {
            $query .= "
            AND DATE(jd.created_at) BETWEEN 
                DATE_SUB(CURDATE(), INTERVAL ((? + 1) * 7) DAY)
                AND DATE_SUB(CURDATE(), INTERVAL (? * 7) DAY)
        ";
            $bindings = [$offset, $offset];
        } elseif ($mode === 'weekly') {
            $query .= "
            AND YEARWEEK(jd.created_at, 1) BETWEEN 
                YEARWEEK(DATE_SUB(CURDATE(), INTERVAL ((? + 1) * 8) WEEK), 1)
                AND YEARWEEK(DATE_SUB(CURDATE(), INTERVAL (? * 8) WEEK), 1)
        ";
            $bindings = [$offset, $offset];
        } elseif ($mode === 'monthly') {
            $query .= " AND YEAR(jd.created_at) = (YEAR(CURDATE()) - ?)";
            $bindings = [$offset];
        }

        $query .= " GROUP BY {$periodeFormat} ORDER BY {$periodeFormat}";

        $data = DB::select($query, $bindings);

        return response()->json($data);
    }
}
