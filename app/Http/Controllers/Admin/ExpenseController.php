<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        confirmDelete('Hapus Pengeluaran?', 'Apakah Anda yakin akan menghapus Pengeluaran ini?');

        $query = Expense::query();

        if ($request->search) {
            $query->where('name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
        }

        $user = auth()->user();

        $query->where('store_id', $user->store_id);

        return self::view('admin.expenses.index', [
            'data' => $query->orderBy('created_at', 'DESC')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return self::view('admin.expenses.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'names' => 'required',
            'amounts' => 'required',
            'date' => 'required',
        ]);

        $user = auth()->user();

        foreach ($request->names as $key => $name) {
            if ($key == 0) {
                continue;
            }

            $data = [
                'store_id' => $user->store_id,
                'user_id' => $user->id,
                'name' => $name,
                'amount' => (int) str_replace('.', '', $request->amounts[$key]),
                'date' => Carbon::parse($request->date),
            ];

            Expense::create($data);
        }

        Alert::success('Berhasil', 'Pengeluaran berhasil ditambahkan');

        return redirect()->route('admin.expenses.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $expense = Expense::find($id);

        return self::view('admin.expenses.form', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'amount' => 'required',
            'date' => 'required',
        ]);

        $expense = Expense::find($id);

        $expense->update([
            'name' => $request->name,
            'amount' => (int) str_replace('.', '', $request->amount),
            'date' => Carbon::parse($request->date),
        ]);

        Alert::success('Berhasil', 'Pengeluaran berhasil diperbarui');

        return redirect()->route('admin.expenses.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
