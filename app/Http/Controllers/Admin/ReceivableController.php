<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receivable;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {

        $query = DB::table('customers as cust')
            ->join('receivables as rcv', function ($join) {
                $join->on('cust.id', '=', 'rcv.customer_id')
                    ->on('cust.store_id', '=', 'rcv.store_id');
            })
            ->select('cust.id','cust.name', DB::raw('SUM(rcv.total_due - rcv.total_paid) as jumlah_utang'))
            ->groupBy('cust.id', 'cust.name');
        //$query = Receivable::query();

        if ($request->search) {
            $query->where('cust.name', env('DB_SEARCH_OPERATOR'), "%$request->search%");
        }

        $user = auth()->user();

        $query->where('cust.store_id', $user->store_id);

        return view('admin.receivables.index', [
            'data' => $query->orderBy('cust.created_at', 'DESC')->paginate(10)
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
