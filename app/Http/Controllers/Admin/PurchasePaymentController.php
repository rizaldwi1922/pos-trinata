<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\JournalEntry;
use App\Models\JournalDetail;

class PurchasePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Purchase $purchase)
    {
        $paymentMethods = PaymentMethod::where('store_id', auth()->user()->store_id)->get();

        return self::view('admin.purchase-payments.form', compact('purchase', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'payment_method_id' => 'required',
            'amount' => 'required',
            'date' => 'required|date',
            'note' => 'nullable',
        ]);

        $user = auth()->user();

        $journalHead = JournalEntry::create([
            'store_id' => $user->store_id,
            'related_table' => 'purchase_payments',
            'description' => 'Pembayaran utang',
        ]);
        $total = (int) str_replace('.', '', $request->amount);
        // piutang usaha
        JournalDetail::create([
            'store_id' => $user->store_id,
            'journal_id' => $journalHead->id,
            'account_code' => '2010',
            'debit' => $total,
        ]);
        // kas
        JournalDetail::create([
            'store_id' => $user->store_id,
            'journal_id' => $journalHead->id,
            'account_code' => '1010',
            'credit' => $total,
        ]);

        PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'user_id' => $user->id,
            'payment_method_id' => $request->payment_method_id,
            'amount' => $total,
            'date' => $request->date,
            'note' => $request->note,
            'store_id' => $user->store_id,
            'journal_id' => $journalHead->id,
        ]);

        Alert::success('Berhasil', 'Pembayaran berhasil disimpan');

        return redirect()->route('admin.purchases.show', $purchase->id);
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
        $purchasePayment = PurchasePayment::findOrFail($id);

        $purchasePayment->delete();

        Alert::success('Berhasil', 'Pembayaran berhasil dihapus');

        return redirect()->back();
    }
}
