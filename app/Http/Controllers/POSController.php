<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class POSController extends APIController
{
    public function index(Request $request)
    {
        // for barcode search
        if ($request->ajax() && $request->has('barcode')) {
            $is_variant = DB::table('product_variants')->where('code', $request->barcode)->exists();
            $is_ingredient = false;
            if (!$is_variant) {
                $is_ingredient = DB::table('products')->where('code', $request->barcode)->exists();
            }
            // if not found
            if (!$is_variant && !$is_ingredient) {
                return null;
            }
            if ($is_variant) {
                $data = DB::table('product_variants')
                    ->select('products.id as product_id', 'product_variants.id as variant_id', 'products.name', 'product_variants.code', 'product_variants.sell_price', 'products.image')
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->where('product_variants.code', $request->barcode)->first();
                $data->is_variant = 1;
            }
            if ($is_ingredient) {
                $data = DB::table('products')
                    ->select('id as product_id', 'name', 'image', 'sell_price', 'code')
                    ->where('code', $request->barcode)->first();
                $data->variant_id = null;
                $data->is_variant = 0;
            }
            return self::validateData($data, ['image::public'])['data'][0];
        }

        // check if user is super admin
        $user = auth()->user();

        if ($user->hasRole('super-admin')) {
            Alert::error('Error', 'Super Admin tidak bisa menggunakan POS');

            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()->route('auth.login');
        }

        return view('app.point-of-sales.index');
    }
}
