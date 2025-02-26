<?php

namespace App\Http\Controllers\Webmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterUserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('user_register_manuals');

        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('email', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('phone', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $data = $query->orderBy('id', 'DESC')->paginate(10);

        return self::view('web.admin.register-user.index', [
            'data' => $data
        ]);
    }
}
