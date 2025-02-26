<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IngredientStock;
use App\Models\ProductIngredientRelation;
use App\Models\ProductIngredientStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProductIngredientStockController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Stok?', 'Apakah Anda yakin akan menghapus Stok ini?');

        $query = ProductIngredientStock::query();

        $user = auth()->user();

        $query->join('products', 'product_ingredient_stocks.product_id', '=', 'products.id');

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('products.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('code.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('product_ingredient_stocks.store_id', $user->store_id);

        $query->select('product_ingredient_stocks.*', 'products.name as product_name', 'products.code as product_code');

        return self::view('admin.product-ingredient-stocks.index', [
            'data' => $query->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.product-ingredient-stocks.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'code' => 'nullable',
            'amount_added' => 'required',
            'expiry_date' => 'nullable|date',
        ]);

        $user = auth()->user();

        $ingredientRelations = ProductIngredientRelation::where('product_id', $request->product_id)->pluck('amount', 'ingredient_id')->toArray();

        ProductIngredientStock::create([
            'store_id' => $user->store_id,
            'product_id' => $request->product_id,
            'user_id' => $user->id,
            'code' => $request->code ?? 'SPB-' . Carbon::now()->format('YmdHis'),
            'amount_added' => $request->amount_added,
            'amount_available' => $request->amount_added,
            'expired_at' => $request->expiry_date ? Carbon::parse($request->expiry_date) : null,
        ]);

        foreach ($ingredientRelations as $ingredientId => $amount) {
            IngredientStock::create([
                'store_id' => $user->store_id,
                'ingredient_id' => $ingredientId,
                'supplier_id' => $request->supplier_id,
                'user_id' => $user->id,
                'code' => $request->code ?? 'STB-' . Carbon::now()->format('YmdHis'),
                'amount_added' => $request->amount_added * $amount,
                'amount_available' => $request->amount_added * $amount,
                'expired_at' => $request->expiry_date ? Carbon::parse($request->expiry_date) : null,
            ]);
        }


        Alert::success('Berhasil', 'Stok Produk Bahan berhasil ditambahkan');

        return redirect()->route('admin.products.ingredient-stocks.index');
    }

    public function destroy(string $id)
    {
        $productStock = ProductIngredientStock::find($id);

        $productStock->delete();

        Alert::success('Berhasil', 'Stok Produk Bahan berhasil dihapus');

        return redirect()->route('admin.products.ingredient-stocks.index');
    }
}
