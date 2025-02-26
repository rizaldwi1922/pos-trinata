<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionExport implements FromCollection, WithHeadings
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

        return Transaction::whereBetween('created_at', [$this->start_date, $this->end_date])
            ->where('store_id', auth()->user()->store_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($trx) {
                $res = array();
                $res['payment_method'] = $trx->paymentMethod->name;
                $res['customer'] = $trx->customer->name ?? '';
                $res['amount_total'] = 'Rp' . number_format($trx->amount_total, 2);
                $res['amount_profit'] = 'Rp' . number_format( $trx->amount_profit, 2);
                $res['total_items'] = $trx->total_items;
                $res['created_at'] = $trx->created_at;
                return $res;
            });
    }
    public function headings(): array
    {
        return [
            'Metode Pembayaran',
            'Pelanggan',
            'Laba Kotor',
            'Laba Bersih',
            'Total Item',
            'Tanggal',
        ];
    }
}
