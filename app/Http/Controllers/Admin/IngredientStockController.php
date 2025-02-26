<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IngredientStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class IngredientStockController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Stok?', 'Apakah Anda yakin akan menghapus Stok ini?');

        $query = IngredientStock::query();

        $user = auth()->user();

        $query->join('product_ingredients', 'ingredient_stocks.ingredient_id', '=', 'product_ingredients.id');

        $query->leftJoin('suppliers', 'ingredient_stocks.supplier_id', '=', 'suppliers.id');

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('product_ingredients.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('suppliers.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('ingredient_stocks.store_id', $user->store_id);

        $query->select('ingredient_stocks.*', 'suppliers.name as supplier_name', 'product_ingredients.name as ingredient_name', 'product_ingredients.code as ingredient_code');

        return self::view('admin.ingredient-stocks.index', [
            'data' => $query->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.ingredient-stocks.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable',
            'ingredient_id' => 'required',
            'code' => 'nullable',
            'amount_added' => 'required',
            'expiry_date' => 'nullable|date',
        ]);

        $user = auth()->user();

        IngredientStock::create([
            'store_id' => $user->store_id,
            'ingredient_id' => $request->ingredient_id,
            'supplier_id' => $request->supplier_id,
            'user_id' => $user->id,
            'code' => $request->code ?? 'STB-' . Carbon::now()->format('YmdHis'),
            'amount_added' => $request->amount_added,
            'amount_available' => $request->amount_added,
            'expired_at' => $request->expiry_date ? Carbon::parse($request->expiry_date) : null,
        ]);


        Alert::success('Berhasil', 'Stok Bahan berhasil ditambahkan');

        return redirect()->route('admin.ingredients-stocks.index');
    }

    public function destroy(string $id)
    {
        $productStock = IngredientStock::find($id);

        $productStock->delete();

        Alert::success('Berhasil', 'Stok berhasil dihapus');

        return redirect()->route('admin.ingredients-stocks.index');
    }
}
