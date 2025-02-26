<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientRelation;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Produk?', 'Apakah Anda yakin akan menghapus Produk ini?');

        $query = Product::query();

        $user = auth()->user();

        $query->join('product_categories', 'products.category_id', '=', 'product_categories.id')->select('products.*');

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('products.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('product_categories.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('products.store_id', $user->store_id);

        return self::view('admin.products.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.products.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'units' => 'required',
            'buy_price' => 'nullable',
            'sell_price' => 'nullable',
            'buy_prices' => 'required',
            'sell_prices' => 'required',
            'sell_retail_prices' => 'required',
            'measurements' => 'required',
            'ingredient_ids' => 'required',
            'amounts' => 'required',
            'code' => 'nullable',
            'codes' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imageUrl' => 'nullable:url',
        ]);

        DB::beginTransaction();

        $user = Auth::user();

        $data = $request->only('name', 'category_id');

        $data['store_id'] = $user->store_id;

        if ($request->imageUrl) {
            if (strlen($request->imageUrl) > 255) {
                Alert::error('Error', 'URL gambar terlalu panjang');

                DB::rollBack();

                return redirect()->back();
            }

            $data['image'] = $request->imageUrl;
        } else if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'products');
        }

        if ($request->has('is_ingredient')) {
            $code = $request->code ?? 'P-' . rand(10000000, 99999999);
            if (!self::isAvailableCode($code)) {
                Alert::error('Error', 'Kode seri sudah digunakan');

                DB::rollBack();

                return redirect()->back();
            }
            $data['code'] = $code;
            $data['buy_price'] = (int) str_replace('.', '', $request->buy_price);
            $data['sell_price'] = (int) str_replace('.', '', $request->sell_price);
        }

        $product = Product::create($data);

        $count = $request->has('is_variant') ? count($request->units) : count($request->ingredient_ids);

        for ($i = 0; $i < $count; $i++) {
            if ($request->has('is_variant')) {
                $code = $request->codes[$i] ?? ('V-' . rand(10000000, 99999999));

                // check if code is already exists
                if (!self::isAvailableCode($code)) {
                    Alert::error('Error', 'Kode seri sudah digunakan');

                    DB::rollBack();

                    return redirect()->back();
                }

                try {
                    ProductVariant::create([
                        'store_id' => $user->store_id,
                        'product_id' => $product->id,
                        'unit_id' => $request->units[$i],
                        'buy_price' => (int) str_replace('.', '', $request->buy_prices[$i]),
                        'sell_price' => (int) str_replace('.', '', $request->sell_prices[$i]),
                        'sell_retail_price' => (int) str_replace('.', '', $request->sell_retail_prices[$i]),
                        'code' => $request->codes[$i] ?? ('V-' . rand(10000000, 99999999)),
                        'measurement' => $request->measurements[$i],
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();

                    Alert::error('Error', 'Terjadi kesalahan: ' . $e->getMessage());

                    return redirect()->back();
                }
            } else if ($request->has('is_ingredient')) {
                // create product ingredient relation
                ProductIngredientRelation::create([
                    'product_id' => $product->id,
                    'ingredient_id' => $request->ingredient_ids[$i],
                    'amount' => $request->amounts[$i],
                ]);
            } else {
                Alert::error('Error', 'Pilih jenis produk');

                DB::rollBack();

                return redirect()->back();
            }
        }

        DB::commit();

        Alert::success('Success', 'Produk berhasil ditambahkan');

        return redirect()->route('admin.products.index');
    }

    public function isAvailableCode($code)
    {
        if (ProductVariant::where('code', $code)->exists() || Product::where('code', $code)->exists()) {
            return false;
        }

        return true;
    }

    public function edit(Product $product)
    {
        return self::view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'units' => 'nullable',
            'buy_price' => 'nullable',
            'sell_price' => 'nullable',
            'buy_prices' => 'nullable',
            'sell_prices' => 'nullable',
            'sell_retail_prices' => 'nullable',
            'measurements' => 'nullable',
            'ingredients' => 'nullable',
            'amounts' => 'nullable',
            'code' => 'nullable',
            'codes' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'imageUrl' => 'nullable:url',
            'variant_ids' => 'nullable',
            'ingredient_ids' => 'nullable',
        ]);

        DB::beginTransaction();

        $user = Auth::user();

        $data = $request->only('name', 'category_id');

        $data['store_id'] = $user->store_id;

        if ($request->imageUrl) {
            if (strlen($request->imageUrl) > 255) {
                Alert::error('Error', 'URL gambar terlalu panjang');

                DB::rollBack();

                return redirect()->back();
            }

            $data['image'] = $request->imageUrl;
        } else if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'products');
        }

        if ($request->has('ingredient_ids')) {
            if ($request->code && $request->code != $product->code) {
                if (!self::isAvailableCode($request->code)) {
                    Alert::error('Error', 'Kode seri sudah digunakan');

                    DB::rollBack();

                    return redirect()->back();
                }
                $data['code'] = $request->code ?? $product->code;
            }
            $data['buy_price'] = (int) str_replace('.', '', $request->buy_price);
            $data['sell_price'] = (int) str_replace('.', '', $request->sell_price);
        }

        $product->update($data);

        if ($request->has('ingredient_ids')) {
            // get ingredient ids except null
            $ingredientIds = array_filter($request->ingredient_ids);

            // delete ingredients that are not in the ingredient ids
            ProductIngredientRelation::where('product_id', $product->id)->whereNotIn('ingredient_id', $ingredientIds)->delete();

            foreach ($request->ingredient_ids as $key => $ingredientId) {
                // create product ingredient relation
                ProductIngredientRelation::updateOrCreate([
                    'product_id' => $product->id,
                    'ingredient_id' => $ingredientId,
                ], [
                    'amount' => $request->amounts[$key],
                ]);
            }
        } else {
            // get variant ids except null
            $variantIds = array_filter($request->variant_ids);

            // delete stocks that are not in the variant ids
            ProductStock::where('product_id', $product->id)->whereNotIn('variant_id', $variantIds)->delete();

            // delete variants that are not in the variant ids
            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $variantIds)->delete();

            foreach ($request->variant_ids as $key => $variantId) {
                $code = $request->codes[$key] ?? 'V-' . rand(10000000, 99999999);

                // check if code is already exists
                if (ProductVariant::where('code', $code)->where('id', '!=', $variantId)->exists() || Product::where('code', $code)->exists()) {
                    Alert::error('Error', 'Kode seri sudah digunakan');

                    DB::rollBack();

                    return redirect()->back();
                }

                ProductVariant::updateOrCreate([
                    'id' => $variantId,
                ], [
                    'store_id' => $user->store_id,
                    'product_id' => $product->id,
                    'unit_id' => $request->units[$key],
                    'buy_price' => (int) str_replace('.', '', $request->buy_prices[$key]),
                    'sell_price' => (int) str_replace('.', '', $request->sell_prices[$key]),
                    'sell_retail_price' => (int) str_replace('.', '', $request->sell_retail_prices[$key]),
                    'measurement' => $request->measurements[$key],
                    'code' => $code
                ]);
            }
        }

        DB::commit();

        Alert::success('Success', 'Produk berhasil diperbarui');

        return redirect()->back();
    }

    public function barcode(Request $request)
    {
        // get product variants
        $product_variants = ProductVariant::select('products.id', 'products.name', 'product_variants.code', 'product_variants.measurement', 'product_units.symbol as unit_name')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
            ->where('product_variants.store_id', auth()->user()->store_id);
        if ($request->ids) {
            $product_variants->whereIn('products.id', explode(',', $request->ids));
        }
        $product_variants = $product_variants->get();

        // get product ingredients
        $product_ingredients = Product::where('code', '!=', null)
            ->where('store_id', auth()->user()->store_id)
            ->select('id', 'name', 'code', 'buy_price', 'sell_price');
        if ($request->ids) {
            $product_ingredients->whereIn('id', explode(',', $request->ids));
        }
        $product_ingredients = $product_ingredients->get();

        // merge product variants and product ingredients
        $products = [];
        foreach ($product_variants as $product) {
            $products[] = $product;
        }

        foreach ($product_ingredients as $product) {
            $products[] = [
                'name' => $product->name,
                'code' => $product->code,
                'measurement' => null,
                'unit_name' => null,
            ];
        }

        // order products by name
        usort($products, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return self::view('admin.products.barcode', [
            'products' => $products,
            'assetOnly' => true
        ]);
    }

    public function destroy(Product $product)
    {
        if (DB::table('transaction_logs')->where('product_id', $product->id)->exists()) {
            Alert::error('Error', 'Produk tidak bisa dihapus karena sudah ada transaksi');

            return redirect()->route('admin.products.index');
        }

        $product->delete();

        Alert::success('Success', 'Produk berhasil dihapus');

        return redirect()->route('admin.products.index');
    }
}
