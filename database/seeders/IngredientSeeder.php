<?php

namespace Database\Seeders;

use App\Models\IngredientStock;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientStock;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::where('name', 'Demo POS')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $supplier = Supplier::where('name', 'Supplier')->first();

        $ingredient_products = [
            [
                'unit' => 'Gram' ,
                'name' => 'Gula Pasir',
                'code' => 'B-' . rand(10000000, 99999999),
                'price' => 2000
            ],
            [
                'unit' => 'Sachet',
                'name' => 'Masako',
                'code' => 'B-' . rand(10000000, 99999999),
                'price' => 1000
            ],
            [
                'unit' => 'Sachet',
                'name' => 'Susu Kental Manis',
                'code' => 'B-' . rand(10000000, 99999999),
                'price' => 3000
            ],
            [
                'unit' => 'Butir',
                'name' => 'Telur Ayam',
                'code' => 'B-' . rand(10000000, 99999999),
                'price' => 1500
            ],
            [
                'unit' => 'Roll',
                'name' => 'Kain',
                'code' => 'B-' . rand(10000000, 99999999),
                'price' => 5000
            ]
        ];

        foreach ($ingredient_products as $ingredient) {
            $unit = ProductUnit::where('name', $ingredient['unit'])->first();
            $item = ProductIngredient::create([
                'store_id' => $store->id,
                'unit_id' => $unit->id,
                'name' => $ingredient['name'],
                'code' => $ingredient['code'],
                'price' => $ingredient['price'],
            ]);

            IngredientStock::create([
                'store_id' => $store->id,
                'user_id' => $admin->id,
                'supplier_id' => $supplier->id,
                'ingredient_id' => $item->id,
                'code' => fake()->numerify('STB-##############'),
                'amount_added' => 100,
                'amount_available' => 100,
                'expired_at' => fake()->randomElement([null, now()->addDays(rand(5, 14))]),
            ]);
        }
    }
}
