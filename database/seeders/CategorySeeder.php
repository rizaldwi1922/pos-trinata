<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::where('name', 'Demo POS')->first();

        $categories = ['Makanan', 'Minuman', 'Snack', 'Pakaian', 'Elektronik', 'Kosmetik'];

        foreach ($categories as $category) {
            ProductCategory::create([
                'name' => $category,
                'store_id' => $store->id,
                'image' => fake()->imageUrl(200, 200),
            ]);
        }
    }
}
