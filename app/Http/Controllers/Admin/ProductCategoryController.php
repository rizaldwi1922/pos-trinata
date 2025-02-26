<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Kategori?', 'Apakah Anda yakin akan menghapus Kategori ini?, Kategori yang dihapus akan menghapus produk yang terkait');

        $query = ProductCategory::query();

        $user = auth()->user();

        if ($request->search) {
            $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
        }

        $query->where('store_id', $user->store_id);

        return self::view('admin.product-categories.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        return self::view('admin.product-categories.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->only('name');

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'categories');
        }

        $data['store_id'] = auth()->user()->store_id;

        ProductCategory::create($data);

        Alert::success('Berhasil', 'Kategori berhasil ditambahkan');

        return redirect()->route('admin.products.categories.index');
    }

    public function edit(string $id)
    {
        $productCategory = ProductCategory::find($id);

        return self::view('admin.product-categories.form', [
            'category' => $productCategory
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $productCategory = ProductCategory::find($id);

        $data = $request->only('name');

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'categories');
        }

        $productCategory->update($data);

        Alert::success('Berhasil', 'Kategori berhasil diperbarui');

        return redirect()->route('admin.products.categories.index');
    }

    public function destroy(string $id)
    {
        $productCategory = ProductCategory::find($id);

        if (Product::where('category_id', $productCategory->id)->exists()) {
            Alert::error('Error', 'Kategori tidak bisa dihapus karena sudah memiliki produk');

            return redirect()->route('admin.products.categories.index');
        }

        $productCategory->delete();

        Alert::success('Berhasil', 'Kategori berhasil dihapus');

        return redirect()->route('admin.products.categories.index');
    }
}
