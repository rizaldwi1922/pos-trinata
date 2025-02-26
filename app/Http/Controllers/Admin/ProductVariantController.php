<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        confirmDelete('Hapus Produk Varian?', 'Apakah Anda yakin akan menghapus Produk Varian ini?');

        $query = ProductVariant::query();

        $user = auth()->user();

        $query->select(
            'products.id as product_id',
            'product_variants.id as variant_id',
            'products.name',
            'product_variants.code',
            'product_variants.measurement',
            'product_variants.buy_price',
            'product_variants.sell_price',
            'product_variants.sell_retail_price',
            'product_units.name as unit_name',
        );

        // join
        $query->join('products', 'product_variants.product_id', '=', 'products.id');
        $query->join('product_units', 'product_variants.unit_id', '=', 'product_units.id');

        // where
        $query->where('product_variants.store_id', $user->store_id);
        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('product_variants.product_id', $request->product_id);
        }
        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('products.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('product_variants.code', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $products = Product::where('store_id', $user->store_id)->orderBy('name', 'ASC')->pluck('name', 'id');

        return self::view('admin.product-variants.index', [
            'data' => $query->orderBy('products.name', 'ASC')->paginate(10),
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('store_id', auth()->user()->store_id)->orderBy('name', 'ASC')->get();

        return self::view('admin.product-variants.form', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'units' => 'required',
            'buy_prices' => 'required',
            'sell_prices' => 'required',
            'sell_retail_prices' => 'required',
            'measurements' => 'required',
            'codes' => 'required',
        ]);

        DB::beginTransaction();

        $user = auth()->user();

        $product = Product::find($request->product_id);

        foreach ($request->variant_ids as $key => $variantId) {
            $code = $request->codes[$key] ?? 'V-' . rand(10000000, 99999999);

            // check if code is already exists
            if (ProductVariant::where('code', $code)->exists() || Product::where('code', $code)->exists()) {
                Alert::error('Error', 'Kode seri sudah digunakan');

                DB::rollBack();

                return redirect()->back();
            }

            // create product variant
            ProductVariant::create([
                'store_id' => $user->store_id,
                'product_id' => $product->id,
                'unit_id' => $request->units[$key],
                'buy_price' => str_replace('.', '', $request->buy_prices[$key]),
                'sell_price' => str_replace('.', '', $request->sell_prices[$key]),
                'sell_retail_price' => $request->sell_retail_prices[$key] ? str_replace('.', '', $request->sell_retail_prices[$key]) : null,
                'measurement' => $request->measurements[$key],
                'code' => $code
            ]);
        }

        DB::commit();

        Alert::success('Success', 'Produk Varian berhasil ditambahkan');

        return redirect()->route('admin.products.variants.index');
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
        $variant = ProductVariant::find($id);

        return self::view('admin.product-variants.form', compact('variant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'variant_ids' => 'required',
            'units' => 'required',
            'buy_prices' => 'required',
            'sell_prices' => 'required',
            'sell_retail_prices' => 'required',
            'measurements' => 'required',
            'codes' => 'required',
        ]);

        foreach ($request->variant_ids as $key => $variantId) {
            $code = $request->codes[$key] ?? 'V-' . rand(10000000, 99999999);

            // check if code is already exists
            if (ProductVariant::where('code', $code)->where('id', '!=', $variantId)->exists() || Product::where('code', $code)->exists()) {
                Alert::error('Error', 'Kode seri sudah digunakan');

                return redirect()->back();
            }

            // update product variant
            ProductVariant::where('id', $variantId)->update([
                'unit_id' => $request->units[$key],
                'buy_price' => str_replace('.', '', $request->buy_prices[$key]),
                'sell_price' => str_replace('.', '', $request->sell_prices[$key]),
                'sell_retail_price' => $request->sell_retail_prices[$key] ? str_replace('.', '', $request->sell_retail_prices[$key]) : null,
                'measurement' => $request->measurements[$key],
                'code' => $code
            ]);
        }

        Alert::success('Success', 'Produk Varian berhasil diperbarui');

        return redirect()->route('admin.products.variants.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $variant = ProductVariant::find($id);

        $variant->delete();

        Alert::success('Success', 'Produk Varian berhasil dihapus');

        return redirect()->route('admin.products.variants.index');
    }
}
