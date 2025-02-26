<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class WebController extends Controller
{
    public function home()
    {
        return view('web.home', ['ignoreNavbar' => true]);
    }

    public function features()
    {
        return view('web.features');
    }

    public function pricing()
    {
        return view('web.pricing');
    }

    public function news()
    {
        return view('web.news');
    }

    public function newsDetail($slug)
    {
        $news = News::where('slug', $slug)->first();

        return view('web.news-detail', compact('news'));
    }

    public function register()
    {
        return view('web.register');
    }

    public function postRegister(Request $request)
    {
        try {
            DB::table('user_register_manuals')->insert([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'description' => $request->description
            ]);
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }

        Alert::success('Success', 'Berhasil daftar, tim kami akan segera menghubungi Anda');

        return redirect()->route('web.home');
    }
}
