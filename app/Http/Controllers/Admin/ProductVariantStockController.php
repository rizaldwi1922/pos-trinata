<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProductVariantStockController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Stok?', 'Apakah Anda yakin akan menghapus Stok ini?');

        $query = ProductStock::query();

        $user = auth()->user();

        $query->join('products', 'product_stocks.product_id', '=', 'products.id');

        $query->leftJoin('suppliers', 'product_stocks.supplier_id', '=', 'suppliers.id');

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('products.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('suppliers.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('product_stocks.store_id', $user->store_id);

        $query->select('product_stocks.*');

        return self::view('admin.product-variant-stocks.index', [
            'data' => $query->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.product-variant-stocks.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'variant_id' => 'required',
            'supplier_id' => 'nullable',
            'code' => 'nullable',
            'amount_added' => 'required',
            'expiry_date' => 'nullable|date',
        ]);

        $user = auth()->user();

        $variant = ProductVariant::find($request->variant_id);

        $product = $variant->product;

        ProductStock::create([
            'store_id' => $user->store_id,
            'product_id' => $product->id,
            'variant_id' => $request->variant_id,
            'supplier_id' => $request->supplier_id,
            'user_id' => $user->id,
            'code' => $request->code ?? 'STV-' . Carbon::now()->format('YmdHis'),
            'amount_added' => $request->amount_added,
            'amount_available' => $request->amount_added,
            'expired_at' => $request->expiry_date ? Carbon::parse($request->expiry_date) : null,
        ]);

        Alert::success('Berhasil', 'Stok berhasil ditambahkan');

        return redirect()->route('admin.products.variant-stocks.index');
    }

    public function destroy(string $id)
    {
        $productStock = ProductStock::find($id);

        $productStock->delete();

        Alert::success('Berhasil', 'Stok berhasil dihapus');

        return redirect()->route('admin.products.variant-stocks.index');
    }

}
