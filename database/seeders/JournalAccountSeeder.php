<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('journal_accounts')->insert([
            [
                'code' => '1010',
                'name' => 'Kas',
                'type' => 'ASSET',
                'description' => 'Digunakan saat menerima atau mengeluarkan uang tunai',
            ],
            [
                'code' => '1020',
                'name' => 'Piutang Usaha',
                'type' => 'ASSET',
                'description' => 'Digunakan saat reseller mengambil barang tetapi belum membayar',
            ],
            [
                'code' => '1030',
                'name' => 'Persediaan Barang Dagangan',
                'type' => 'ASSET',
                'description' => 'Digunakan untuk mencatat stok barang yang ada',
            ],
            [
                'code' => '1040',
                'name' => 'Uang Muka ke Supplier',
                'type' => 'ASSET',
                'description' => 'Digunakan saat membayar uang muka ke supplier',
            ],
            [
                'code' => '2010',
                'name' => 'Utang Usaha',
                'type' => 'LIABILITY',
                'description' => 'Digunakan saat menerima barang dari supplier tetapi belum membayar',
            ],
            [
                'code' => '4010',
                'name' => 'Pendapatan Penjualan Eceran',
                'type' => 'REVENUE',
                'description' => 'Digunakan saat customer biasa membeli barang',
            ],
            [
                'code' => '4020',
                'name' => 'Pendapatan Penjualan Grosir',
                'type' => 'REVENUE',
                'description' => 'Digunakan saat reseller membeli barang',
            ],
            [
                'code' => '5010',
                'name' => 'Harga Pokok Penjualan (HPP)',
                'type' => 'EXPENSE',
                'description' => 'Digunakan untuk mencatat biaya barang yang dijual',
            ],
            [
                'code' => '5020',
                'name' => 'Beban Sewa',
                'type' => 'EXPENSE',
                'description' => 'Digunakan untuk mencatat biaya sewa toko',
            ],
            [
                'code' => '5030',
                'name' => 'Beban Listrik',
                'type' => 'EXPENSE',
                'description' => 'Digunakan untuk mencatat biaya listrik toko',
            ],
            [
                'code' => '5040',
                'name' => 'Beban Gaji Pegawai',
                'type' => 'EXPENSE',
                'description' => 'Digunakan untuk mencatat gaji karyawan toko',
            ],
            [
                'code' => '5050',
                'name' => 'Beban Transportasi',
                'type' => 'EXPENSE',
                'description' => 'Digunakan untuk mencatat biaya pengiriman barang',
            ],
            [
                'code' => '5090',
                'name' => 'Beban Lain-lain',
                'type' => 'EXPENSE',
                'description' => 'Digunakan untuk mencatat pengeluaran yang tidak termasuk dalam kategori beban lainnya',
            ]
        ]);
        
    }
}
