<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Pegawai?', 'Apakah Anda yakin akan menghapus Pegawai ini?');

        $query = User::query();

        $user = auth()->user();

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('email', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('id', '!=', $user->id);

        $query->where('store_id', $user->store_id);

        return self::view('admin.users.index', [
            'data' => $query->orderBy('name', 'ASC')->paginate(10)
        ]);
    }

    public function create()
    {
        $roles = Role::orderBy('name', 'ASC')
            ->whereNotIn('name', ['super-admin', 'web-admin'])
            ->paginate();

        return self::view('admin.users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);

        $data = $request->only('name', 'email');

        $data['store_id'] = auth()->user()->store_id;

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $role = Role::find($request->role_id);

        $user->assignRole($role->name);

        Alert::success('Berhasil', 'Pegawai berhasil ditambahkan');

        return redirect()->route('admin.users.index');
    }

    public function edit(string $id)
    {
        $user = User::find($id);

        $roles = Role::orderBy('name', 'ASC')
            ->whereNotIn('name', ['super-admin', 'web-admin'])
            ->paginate();

        return self::view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user = User::find($id);

        $data = $request->only('name', 'email');

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $role = Role::find($request->role_id);

        $user->update($data);

        $user->syncRoles([$role->name]);

        Alert::success('Berhasil', 'Pegawai berhasil diubah');

        return redirect()->route(auth()->user()->hasRole('super-admin') ? 'super.stores.index' : 'admin.users.index');
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        $user->delete();

        Alert::success('Berhasil', 'Pegawai berhasil dihapus');

        return redirect()->route('admin.users.index');
    }
}
