<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientStock;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductIngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::where('name', 'Demo POS')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();

        $productIngredients = Product::whereNotNull('sell_price')->get();

        foreach ($productIngredients as $product) {
            ProductIngredientStock::create([
                'store_id' => $store->id,
                'user_id' => $admin->id,
                'product_id' => $product->id,
                'code' => fake()->numerify('SPB-##############'),
                'amount_added' => 100,
                'amount_available' => 100,
                'expired_at' => fake()->randomElement([null, now()->addDays(rand(5, 14))]),
            ]);
        }
    }
}
