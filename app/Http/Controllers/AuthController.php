<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\WebPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Mobile_Detect;

class AuthController extends Controller
{
    public function getLogin()
    {
        return self::view('web.auth.login.index', ['assetOnly' => true]);
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))) {
            $detect = new Mobile_Detect();

            Alert::success('Success', 'Login Berhasil');

            $user = Auth::user();

            if ($user->hasRole('super-admin')) {
                return redirect()->route('super.stores.index');
            } else if ($user->hasRole('web-admin')) {
                return redirect()->route('web.admin.dashboard');
            } else if ($detect->isTablet() || $detect->isMobile()) {
                return redirect()->route('app.point_of_sale');
            }

            return redirect()->route('admin.dashboard');
        }

        Alert::error('Error', 'Alamat Email atau Password Salah');

        return redirect()->back();
    }

    public function getLoginAs(string $id)
    {
        $store = Store::find($id);

        $user = User::where('store_id', $store->id)->orderBy('id', 'ASC')->first();

        Auth::login($user);

        Alert::success('Success', 'Login As Berhasil');

        return redirect()->route('admin.dashboard');
    }

    public function getLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
