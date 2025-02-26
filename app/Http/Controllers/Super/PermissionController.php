<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $data = Permission::orderBy('name', 'ASC')->paginate();

        return self::view('super.permissions.index', compact('data'));
    }
}
