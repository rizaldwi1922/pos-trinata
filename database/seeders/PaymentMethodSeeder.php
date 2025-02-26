<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::where('name', 'Demo POS')->first();

        $payment_method = ['Cash', 'Kartu Debit', 'Kartu Kredit'];

        foreach ($payment_method as $method) {
            PaymentMethod::create([
                'store_id' => $store->id,
                'name' => $method,
                'code' => strtolower(str_replace(' ', '-', $method)),
                'is_cash' => $method === 'Cash',
                'image' => fake()->imageUrl(200, 200),
            ]);
        }


    }
}
