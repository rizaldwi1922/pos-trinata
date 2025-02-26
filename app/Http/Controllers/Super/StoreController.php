<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Toko?', 'Apakah Anda yakin akan menghapus Toko ini?');

        $query = Store::query();

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('code', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        return self::view('super.stores.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        $roles = Role::orderBy('name', 'ASC')
            ->whereNotIn('name', ['super-admin', 'web-admin'])
            ->paginate();

        return self::view('super.stores.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'role_id' => 'required',
            'user_name' => 'required',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required',
        ]);

        $data = $request->only('name', 'code');

        $data['image'] = $this->uploadFile($request->file('image'), 'stores');

        $store = Store::create($data);
        $store->createDefaultStoreSetting();

        $admin = User::create([
            'store_id' => $store->id,
            'name' => $request->user_name,
            'email' => $request->user_email,
            'password' => bcrypt($request->user_password),
            'email_verified_at' => now(),
        ]);

        $role = Role::find($request->role_id);

        $admin->assignRole($role->name);

        Alert::success('Berhasil', 'Toko berhasil ditambahkan');

        return redirect()->route('super.stores.index');
    }

    public function edit(string $id)
    {
        $store = Store::find($id);

        return self::view('super.stores.form', [
            'store' => $store
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $store = Store::find($id);

        $data = $request->only('name', 'code');

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'stores');
        }

        $store->update($data);

        Alert::success('Berhasil', 'Toko berhasil diubah');

        return redirect()->route('super.stores.index');
    }

    public function destroy(string $id)
    {
        $store = Store::find($id);

        if (Transaction::where('store_id', $store->id)->exists()) {
            Alert::error('Gagal', 'Toko tidak bisa dihapus karena memiliki transaksi');

            return redirect()->route('super.stores.index');
        }

        $store->delete();

        Alert::success('Berhasil', 'Toko berhasil dihapus');

        return redirect()->route('super.stores.index');
    }
}
