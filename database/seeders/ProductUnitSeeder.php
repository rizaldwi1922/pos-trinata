<?php

namespace Database\Seeders;

use App\Models\ProductUnit;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::where('name', 'Demo POS')->first();

        $unit_names = ['Kilo Gram', 'Gram', 'Liter', 'Mililiter', 'Pcs', 'Box', 'Pack', 'Botol', 'Bungkus', 'Dus', 'Kardus', 'Kodi', 'Kotak', 'Lusin', 'Paket', 'Papan', 'Rim', 'Roll', 'Sachet', 'Set', 'Butir'];

        $unit_symbol = ['Kg', 'g', 'L', 'ml', 'Pcs', 'Box', 'Pack', 'Botol', 'Bks', 'Dus', 'Kardus', 'Kodi', 'Kotak', 'Lusin', 'Paket', 'Papan', 'Rim', 'Roll', 'Sachet', 'Set', 'Butir'];

        foreach ($unit_names as $key => $unit_name) {
            ProductUnit::create([
                'name' => $unit_name,
                'symbol' => $unit_symbol[$key],
                'store_id' => $store->id,
            ]);
        }
    }
}
