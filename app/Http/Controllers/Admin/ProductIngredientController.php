<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class ProductIngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        confirmDelete('Hapus Produk Bahan?', 'Apakah Anda yakin akan menghapus Produk Bahan ini?');

        $query = Product::query();

        $user = auth()->user();

        // where
        $query->where('products.store_id', $user->store_id);
        $query->whereNotNull('products.sell_price');

        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('products.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        return self::view('admin.product-ingredients.index', [
            'data' => $query->orderBy('products.name', 'ASC')->paginate(10),
        ]);
    }

    public function destroy(Product $product)
    {
        if (DB::table('transaction_logs')->where('product_id', $product->id)->exists()) {
            Alert::error('Error', 'Produk Bahan tidak bisa dihapus karena sudah ada transaksi');

            return redirect()->route('admin.products.index');
        }

        $product->delete();

        Alert::success('Success', 'Produk berhasil dihapus');

        return redirect()->route('admin.products.index');
    }
}
