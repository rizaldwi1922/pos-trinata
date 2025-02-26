<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductIngredient;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        confirmDelete('Hapus Bahan Produk?', 'Apakah Anda yakin akan menghapus Bahan Produk ini?');

        $query = ProductIngredient::query();

        $user = auth()->user();

        $query->select(
            'product_ingredients.id',
            'product_ingredients.name',
            'product_ingredients.code',
            'product_ingredients.price',
            'product_units.name as unit_name',
        );

        // join
        $query->join('product_units', 'product_ingredients.unit_id', '=', 'product_units.id');

        // where
        $query->where('product_ingredients.store_id', $user->store_id);
        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('product_ingredients.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('product_ingredients.code', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        return self::view('admin.ingredients.index', [
            'data' => $query->orderBy('product_ingredients.name', 'ASC')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return self::view('admin.ingredients.form');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productIngredient = ProductIngredient::find($id);

        $transationLogs = DB::table('transaction_logs')
            ->where('ingredient_id', $id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return self::view('admin.ingredients.show', [
            'productIngredient' => $productIngredient,
            'transationLogs' => $transationLogs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'unit_id' => 'required',
            'price' => 'required',
        ]);

        ProductIngredient::create([
            'store_id' => auth()->user()->store_id,
            'name' => $request->name,
            'code' => $request->code ?? 'B-' . rand(10000000, 99999999),
            'price' => (int) str_replace('.', '', $request->price),
            'unit_id' => $request->unit_id,
        ]);

        Alert::success('Berhasil', 'Bahan Produk berhasil ditambahkan');

        return redirect()->route('admin.ingredients.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ingredient = ProductIngredient::find($id);

        return self::view('admin.ingredients.form', compact('ingredient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'nullable',
            'unit_id' => 'required',
            'price' => 'required',
        ]);

        ProductIngredient::find($id)->update([
            'name' => $request->name,
            'code' => $request->code ?? 'B-' . rand(10000000, 99999999),
            'unit_id' => $request->unit_id,
            'price' => (int) str_replace('.', '', $request->price),
        ]);

        Alert::success('Berhasil', 'Bahan Produk berhasil diperbarui');

        return redirect()->route('admin.ingredients.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (DB::table('product_ingredient_relations')->where('ingredient_id', $id)->exists()) {
            Alert::error('Gagal', 'Bahan Produk tidak dapat dihapus karena sedang digunakan');

            return redirect()->route('admin.ingredients.index');
        }

        ProductIngredient::destroy($id);

        Alert::success('Berhasil', 'Bahan Produk berhasil dihapus');

        return redirect()->route('admin.ingredients.index');
    }
}
