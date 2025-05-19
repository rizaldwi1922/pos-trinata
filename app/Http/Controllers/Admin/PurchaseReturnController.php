<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\ProductVariant;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\PurchaseReturnItem;
use App\Models\ProductStock;
use App\Models\PurchasePayment;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $query = PurchaseReturn::with('supplier');

        $user = auth()->user();

        // if ($request->search) {
        //     $query->where('invoice_number', env('DB_SEARCH_OPERATOR'), "%$request->search%");
        // }

        $query->where('store_id', $user->store_id);

        return view('admin.purchase-returns.index', [
            'data' => $query->orderBy('created_at', 'DESC')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $variants = ProductVariant::where('products.store_id', auth()->user()->store_id)
        //     ->select(DB::raw("CONCAT('v,', product_variants.id) as id"), DB::raw("CONCAT(products.name, ' (', product_units.name, ')') as name"))
        //     ->join('products', 'product_variants.product_id', '=', 'products.id')
        //     ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
        //     ->pluck('name', 'product_variants.id');

        $latestStockSubquery = DB::table('product_stocks')
            ->select(DB::raw('MAX(id)'))
            ->where('amount_available', '>', 0)
            ->where('store_id', auth()->user()->store_id)
            ->groupBy('variant_id');

        $variants = DB::table('product_stocks')
            ->join('product_variants', 'product_stocks.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
            ->where('products.store_id', auth()->user()->store_id)
            ->whereIn('product_stocks.id', $latestStockSubquery)
            ->select([
                DB::raw("product_variants.id"),
                DB::raw("CONCAT(products.name, ' (', product_stocks.amount_available ,' ',product_units.name, ')') as name"),
                DB::raw('FLOOR(product_stocks.unit_price) as unit_price'),
                'product_stocks.amount_available'
            ])
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::where('store_id', auth()->user()->store_id)->get();
        return view('admin.purchase-returns.form', compact('suppliers', 'variants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier' => 'required',
            'retur_date' => 'required|date',
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $journalHead = JournalEntry::create([
                'store_id' => $user->store_id,
                'related_table' => 'purchase_returns',
                'description' => 'Retur Barang',
                'entry_date' => $request->retur_date,
            ]);

            $purchaseReturn = PurchaseReturn::create([
                'store_id' => $user->store_id,
                'supplier_id' => $request->supplier,
                'return_date' => $request->retur_date,
                'total_amount' => $request->total,
                'notes' => $request->notes,
                'journal_id' => $journalHead->id,
            ]);

            foreach ($request->items as $index => $key) {

                $is_stock_debt = $request->amounts[$index];

                $availableStock = ProductStock::where('store_id', $user->store_id)
                    ->where('variant_id', $request->items[$index])
                    ->where('amount_available', '>', 0)
                    ->where(function ($query) {
                        $query->whereNull('expired_at')
                            ->orWhere('expired_at', '>', now());
                    })
                    ->sum('amount_available');

                $dStock['amount_before'] = $availableStock;
                $dStock['amount_after'] = $availableStock - $is_stock_debt;
                while ($is_stock_debt > 0) {
                    $stock = ProductStock::where('store_id', $user->store_id)
                        ->where('variant_id', $request->items[$index])
                        ->where('amount_available', '>', 0)
                        ->where(function ($query) {
                            $query->whereNull('expired_at')
                                ->orWhere('expired_at', '>', now());
                        })
                        ->orderBy('created_at', 'ASC')
                        ->first();

                    if (!$stock) {
                        $is_success = false;
                        $txt_error = 'Stock tidak mencukupi';
                        break;
                    }

                    $is_stock_debt -= $stock->amount_available;
                    $availableStock -= $stock->amount_available;

                    if ($is_stock_debt > 0) {
                        $stock->amount_available = 0;
                        $stock->update();
                    } else {
                        $stock->amount_available = abs($is_stock_debt);
                        $stock->update();
                    }
                }

                PurchaseReturnItem::create([
                    'store_id' => $user->store_id,
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_variant_id' => $request->items[$index],
                    'quantity' => $request->amounts[$index],
                    'price' => $request->price[$index],
                ]);

                $this->processReturnRetur($request->supplier, $request->total, $journalHead, $request->retur_date);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }

        DB::commit();
        Alert::success('Success', 'Retur berhasil ditambahkan');

        return redirect()->route('admin.purchase-returns.index');
    }

    public function processReturnRetur($supplier_id, $return_amount, $journalHead, $date)
    {
        $remaining = $return_amount;

        // Ambil semua pembelian supplier yang belum lunas, urut dari yang paling lama
        $purchases = Purchase::where('supplier_id', $supplier_id)
            ->where('store_id', auth()->user()->store_id)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->filter(function ($purchase) {
                return $purchase->remainingDept() > 0;
            });
        
        // persediaan
        JournalDetail::create([
            'store_id' => auth()->user()->store_id,
            'journal_id' => $journalHead->id,
            'account_code' => '1030',
            'credit' => $return_amount,
        ]);

        foreach ($purchases as $purchase) {
            $sisa = $purchase->remainingDept();

            if ($remaining <= 0) {
                break;
            }

            $bayar = min($remaining, $sisa);

            // PurchasePayment::create([
            //     'purchase_id' => $purchase->id,
            //     'amount' => $bayar,
            //     'type' => 'return',
            //     'note' => 'Retur barang dari supplier',
            // ]);
            // piutang usaha
            JournalDetail::create([
                'store_id' => auth()->user()->store_id,
                'journal_id' => $journalHead->id,
                'account_code' => '2010',
                'debit' => $bayar,
            ]);

            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'user_id' => auth()->user()->id,
                'payment_method_id' => 1,
                'amount' => $bayar,
                'date' => $date,
                'type' => 1,
                'note' => 'Retur barang dari supplier',
                'store_id' => auth()->user()->store_id,
                'journal_id' => $journalHead->id,
            ]);

            $remaining -= $bayar;
        }

        // Jika masih sisa retur, masukkan ke kas (supplier mengembalikan uang tunai)
        if ($remaining > 0) {
            JournalDetail::create([
                'store_id' => auth()->user()->store_id,
                'journal_id' => $journalHead->id,
                'account_code' => '1010',
                'debit' => $remaining,
            ]);
        }

        return $remaining;

        // Catat juga di table returns
        // ReturnTransaction::create([
        //     'supplier_id' => $supplier_id,
        //     'amount' => $return_amount,
        //     'applied_amount' => $return_amount - $remaining,
        //     'refunded_amount' => $remaining,
        // ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
