<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SettingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $settings = Setting::where('store_id', $user->store_id)->pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        $store = auth()->user()->store;

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $value = $this->uploadFile($request->file($key), ('settings/' . $store->id));
            }
            Setting::updateOrCreate(
                ['store_id' => $store->id, 'key' => $key],
                ['value' => $value]
            );
        }

        Alert::success('Success', 'Pengaturan berhasil diperbarui');

        return redirect()->back();
    }
}
