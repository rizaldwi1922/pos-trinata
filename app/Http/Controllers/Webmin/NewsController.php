<?php

namespace App\Http\Controllers\Webmin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        confirmDelete('Hapus Blog?', 'Apakah Anda yakin akan menghapus Blog ini?');

        $data = News::orderBy('id', 'DESC')->paginate(10);

        return self::view('web.admin.news.index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return self::view('web.admin.news.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $image = $this->uploadFile($request->file('image'), 'news');

        try {
            News::create([
                'title' => $request->title,
                'content' => $request->content,
                'image' => $image,
            ]);
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }

        Alert::success('Success', 'Berhasil menambahkan berita');

        return redirect()->route('web.admin.news.index');
    }

    public function edit($id)
    {
        $news = News::find($id);

        return self::view('web.admin.news.form', [
            'news' => $news
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $news = News::find($id);

        $image = $news->image;

        if ($request->hasFile('image')) {
            $image = $this->uploadFile($request->file('image'), 'news');
        }

        try {
            $news->update([
                'title' => $request->title,
                'content' => $request->content,
                'image' => $image,
            ]);
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }

        Alert::success('Success', 'Berhasil mengubah berita');

        return redirect()->route('web.admin.news.index');
    }

    public function destroy($id)
    {
        $news = News::find($id);

        $news->delete();

        Alert::success('Success', 'Berhasil menghapus berita');

        return redirect()->route('web.admin.news.index');
    }
}
