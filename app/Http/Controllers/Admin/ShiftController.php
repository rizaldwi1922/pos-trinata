<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Shift::query();

        $user = auth()->user();

        $query->join('users', 'shifts.user_id', '=', 'users.id');

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('users.name', env('DB_SEARCH_OPERATOR'), "%$request->search%")
                    ->orWhere('users.email', env('DB_SEARCH_OPERATOR'), "%$request->search%");
            });
        }

        $query->where('shifts.store_id', $user->store_id);

        $query->select('shifts.*', 'users.name as user_name');

        return self::view('admin.shifts.index', [
            'data' => $query->latest()->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $query = Transaction::query();
        $query->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id');
        $query->leftJoin('customers', 'transactions.customer_id', '=', 'customers.id');
        $query->where('transactions.store_id', $shift->store_id);
        $query->where('shift_id', $shift->id);
        $query->select('transactions.*');

        return self::view('admin.shifts.show', [
            'shift' => $shift,
            'transactions' => $query->latest()->get()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        //
    }
}
