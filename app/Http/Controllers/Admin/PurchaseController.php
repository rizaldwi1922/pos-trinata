<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientStock;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        confirmDelete('Hapus Pembelian?', 'Apakah Anda yakin akan menghapus Pembelian ini?, Stok terkait akan ikut terhapus');

        $query = Purchase::query();

        $user = auth()->user();

        if ($request->search) {
            $query->where('invoice_number', env('DB_SEARCH_OPERATOR'), "%$request->search%");
        }

        $query->where('store_id', $user->store_id);

        return self::view('admin.purchases.index', [
            'data' => $query->orderBy('created_at', 'DESC')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $variants = ProductVariant::where('products.store_id', auth()->user()->store_id)
            ->select(DB::raw("CONCAT('v,', product_variants.id) as id"), DB::raw("CONCAT(products.name, ' (', product_variants.measurement, ' ', product_units.name, ')') as name"))
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
            ->pluck('name', 'product_variants.id');

        $ingredients = ProductIngredient::where('product_ingredients.store_id', auth()->user()->store_id)
            ->select(DB::raw("CONCAT('i,', product_ingredients.id) as id"), DB::raw("CONCAT(product_ingredients.name, ' (', product_units.name, ')') as name"))
            ->join('product_units', 'product_ingredients.unit_id', '=', 'product_units.id')
            ->pluck('name', 'product_ingredients.id');

        $suppliers = Supplier::where('store_id', auth()->user()->store_id)->pluck('name', 'id');

        return self::view('admin.purchases.form', compact('variants', 'ingredients', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required',
            'total' => 'required',
            'due_date' => 'nullable'
        ]);

        $user = auth()->user();

        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'store_id' => $user->store_id,
                'user_id' => $user->id,
                'invoice_number' => $request->invoice_number,
                'due_date' => Carbon::parse($request->due_date),
                'total' => (int) str_replace('.', '', $request->total),
            ]);

            if (count(array_filter($request->items)) == 0) {
                DB::rollBack();

                Alert::error('Error', 'Item tidak boleh kosong');

                return redirect()->back();
            }

            foreach ($request->items as $index => $key) {
                if ($index == 0) {
                    continue;
                }

                if ((!$key || !$request->suppliers[$index] || !$request->amounts[$index])) {
                    DB::rollBack();

                    Alert::error('Error', 'Item pembelian tidak valid');

                    return redirect()->back();
                }

                list($type, $id) = explode(',', $key);

                if ($type == 'v') {
                    $variant = ProductVariant::find($id);

                    ProductStock::create([
                        'store_id' => $user->store_id,
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->id,
                        'user_id' => $user->id,
                        'supplier_id' => $request->suppliers[$index],
                        'purchase_id' => $purchase->id,
                        'code' => $request->codes[$index] ?? 'STV-' . Carbon::now()->format('YmdHis'),
                        'amount_added' => $request->amounts[$index],
                        'amount_available' => $request->amounts[$index],
                        'expired_at' => $request->expiry_dates[$index] ? Carbon::parse($request->expiry_dates[$index]) : null,
                    ]);
                } else {
                    $ingredient = ProductIngredient::find($id);

                    ProductIngredientStock::create([
                        'store_id' => $user->store_id,
                        'ingredient_id' => $ingredient->id,
                        'user_id' => $user->id,
                        'supplier_id' => $request->suppliers[$index],
                        'purchase_id' => $purchase->id,
                        'code' => $request->codes[$index] ?? 'STB-' . Carbon::now()->format('YmdHis'),
                        'amount_added' => $request->amounts[$index],
                        'amount_available' => $request->amounts[$index],
                        'expired_at' => $request->expiry_dates[$index] ? Carbon::parse($request->expiry_dates[$index]) : null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }

        DB::commit();

        Alert::success('Success', 'Pembelian berhasil ditambahkan');

        return redirect()->route('admin.purchases.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        confirmDelete('Hapus Pembayaran?', 'Apakah Anda yakin akan menghapus Pembayaran ini?');

        $purchase = Purchase::findOrFail($id);

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

        $purchasePayments = PurchasePayment::where('purchase_id', $purchase->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return self::view('admin.purchases.show', compact('purchase', 'purchaseDetails', 'purchasePayments'));
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
        $purchase = Purchase::findOrFail($id);

        ProductStock::where('purchase_id', $purchase->id)->delete();

        ProductIngredientStock::where('purchase_id', $purchase->id)->delete();

        $purchase->delete();

        Alert::success('Berhasil', 'Pembelian berhasil dihapus');

        return redirect()->route('admin.purchases.index');
    }
}
