<?php

namespace App\Exports;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseExport implements FromCollection, WithHeadings
{
    private $start_date;
    private $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $this->end_date = date('Y-m-d', strtotime($this->end_date . ' +1 day'));

        return Purchase::whereBetween('created_at', [$this->start_date, $this->end_date])
            ->where('store_id', auth()->user()->store_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($purchase) {
                $res['invoice_number'] = $purchase->invoice_number;

                $isDone = PurchasePayment::where('purchase_id', $purchase->id)
                    ->orderBy('created_at', 'DESC')
                    ->sum('amount') == $purchase->total;

                $res['is_done'] = $isDone ? 'Lunas' : 'Belum Lunas';

                $res['amount_total'] = 'Rp' . number_format($purchase->total, 2);
                $res['due_date'] = $purchase->due_date;
                $res['created_at'] = $purchase->created_at;

                $dataFirst = DB::table('product_stocks')->where('purchase_id', $purchase->id)
                    ->select(DB::raw("CONCAT('v,', product_stocks.id) as id"), 'product_stocks.code', 'suppliers.name as supplier_name', 'products.name as product_name', 'product_variants.measurement as variant_measurement', 'product_units.name as unit_name', 'product_stocks.amount_added', 'product_stocks.amount_available', 'product_stocks.expired_at')
                    ->join('suppliers', 'product_stocks.supplier_id', '=', 'suppliers.id')
                    ->join('products', 'product_stocks.product_id', '=', 'products.id')
                    ->join('product_variants', 'product_stocks.variant_id', '=', 'product_variants.id')
                    ->join('product_units', 'product_variants.unit_id', '=', 'product_units.id')
                    ->get();

                $dataSecond = DB::table('product_ingredient_stocks')->where('purchase_id', $purchase->id)
                    ->select(DB::raw("CONCAT('i,', product_ingredient_stocks.id) as id"), 'product_ingredient_stocks.code', 'suppliers.name as supplier_name', 'product_ingredients.name as ingredient_name', 'product_units.name as unit_name', 'product_ingredient_stocks.amount_added', 'product_ingredient_stocks.amount_available', 'product_ingredient_stocks.expired_at')
                    ->join('suppliers', 'product_ingredient_stocks.supplier_id', '=', 'suppliers.id')
                    ->join('product_ingredients', 'product_ingredient_stocks.ingredient_id', '=', 'product_ingredients.id')
                    ->join('product_units', 'product_ingredients.unit_id', '=', 'product_units.id')
                    ->get();

                $purchaseDetails = $dataFirst->merge($dataSecond);

                $items = [];
                foreach ($purchaseDetails as $detail) {
                    list($type, $id) = explode(',', $detail->id);

                    if ($type == 'v') {
                        $items[] = "{$detail->product_name} ({$detail->variant_measurement} {$detail->unit_name}) (x{$detail->amount_added})";
                    } else {
                        $items[] = "{$detail->ingredient_name} ({$detail->unit_name}) (x{$detail->amount_added})";
                    }
                }
                $res['items'] = implode(', ', $items);

                return $res;
            });
    }
    public function headings(): array
    {
        return [
            'Invoice ID',
            'Status',
            'Total',
            'Jatuh Tempo',
            'Tanggal',
            'Items'
        ];
    }
}
