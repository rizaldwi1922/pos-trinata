<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::where('store_id', $request->user()->store_id)
            ->orderBy('name')
            ->get(['id', 'name', 'balance', 'phone']);

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $customer = Customer::create([
            'store_id' => $request->user()->store_id,
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
        ]);

        return response()->json($customer, 201);
    }
}