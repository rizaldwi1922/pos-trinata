<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        confirmDelete('Hapus Role?', 'Apakah Anda yakin akan menghapus Role ini?');

        $data = Role::orderBy('name', 'ASC')
            ->whereNotIn('name', ['super-admin', 'web-admin'])
            ->paginate();

        return self::view('super.roles.index', compact('data'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name', 'ASC')->get();

        return self::view('super.roles.form', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'permissions' => 'required|array',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();

        $role = Role::create(['name' => $request->name]);

        $role->syncPermissions($permissions);

        Alert::success('Berhasil', 'Role berhasil ditambahkan');

        return redirect()->route('super.roles.index');
    }

    public function edit(string $id)
    {
        $role = Role::find($id);

        $permissions = Permission::orderBy('name', 'ASC')->get();

        return self::view('super.roles.form', compact('role', 'permissions'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'permissions' => 'required|array',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();

        $role = Role::find($id);

        $role->update(['name' => $request->name]);

        $role->syncPermissions($permissions);

        Alert::success('Berhasil', 'Role berhasil diperbarui');

        return redirect()->route('super.roles.index');
    }

    public function destroy(string $id)
    {
        $role = Role::find($id);

        $role->delete();

        Alert::success('Berhasil', 'Role berhasil dihapus');

        return redirect()->route('super.roles.index');
    }
}
