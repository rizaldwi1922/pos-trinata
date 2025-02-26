<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ProductIngredientRelation;
use App\Models\ProductIngredientStock;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::where('name', 'Demo POS')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();
        $supplier = Supplier::where('name', 'Supplier')->first();

        $product_variants = [
            ['name' => 'Sushi Jepang', 'image' => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcToeptaFQrC4AX05fAy1JWyjjlEIAii-teJ6A&usqp=CAU", 'category_id' => 1, 'store_id' => 1, 'variants' => [
                [
                    'unit_id' => 5,
                    'measurement' => 1,
                    'buy_price' => 18000,
                    'sell_price' => 25000,
                ]
            ]],
            ['name' => 'Soda Gembira', 'image' => "https://d1sag4ddilekf6.azureedge.net/compressed_webp/items/IDITE20210703092837113709/detail/menueditor_item_14ad9a16e39346b0949893d9322f49ec_1625387067907135748.webp", 'category_id' => 2, 'store_id' => 1, 'variants' => [
                [
                    'unit_id' => 5,
                    'measurement' => 1,
                    'buy_price' => 7000,
                    'sell_price' => 9000,
                    'sell_retail_price' => 8000,
                ]
            ]],
            ['name' => 'Air Putih', 'image' => "https://images.tokopedia.net/img/cache/500-square/product-1/2020/7/8/2813315/2813315_79feba09-326b-423e-aa84-57591cacca88_500_500.webp", 'category_id' => 2, 'store_id' => 1, 'variants' => [
                [
                    'unit_id' => 2,
                    'measurement' => 500,
                    'buy_price' => 7000,
                    'sell_price' => 7500,
                    'sell_retail_price' => 7200,
                ]
            ]],
            ['name' => 'Kripik', 'image' => "https://img.biggo.com.tw/sxGeoi9vXDEgA-XOVE6t-mWorIPajfXHlT0dJMDCMfxU/https:/filebroker-cdn.lazada.co.id/kf/S8c95028a84d0490cb39d500df4c8d1f1n.jpg", 'category_id' => 3, 'store_id' => 1, 'variants' => [
                [
                    'unit_id' => 2,
                    'measurement' => 200,
                    'buy_price' => 6500,
                    'sell_price' => 8000,
                ], [
                    'unit_id' => 2,
                    'measurement' => 600,
                    'buy_price' => 10000,
                    'sell_price' => 12000,
                ], [
                    'unit_id' => 1,
                    'measurement' => 1,
                    'buy_price' => 12000,
                    'sell_price' => 13000,
                ]
            ]],
            ['name' => 'Takoyaki', 'image' => "https://cdn.shopify.com/s/files/1/0508/4312/3903/products/Octopus-Balls_2_512x512.jpg?v=1634629010", 'category_id' => 3, 'store_id' => 1, 'variants' => [
                [
                    'product_id' => 8,
                    'unit_id' => 5,
                    'measurement' => 1,
                    'buy_price' => 19000,
                    'sell_price' => 21000,
                ]
            ]]
        ];

        foreach ($product_variants as $product) {
            $obj = Product::create([
                'store_id' => 1,
                'name' => $product['name'],
                'image' => $product['image'],
                'category_id' => $product['category_id'],
                'store_id' => $product['store_id'],
            ]);

            foreach ($product['variants'] as $variant) {
                $item = ProductVariant::create([
                    'store_id' => $store->id,
                    'product_id' => $obj->id,
                    'unit_id' => $variant['unit_id'],
                    'measurement' => $variant['measurement'],
                    'buy_price' => $variant['buy_price'],
                    'sell_price' => $variant['sell_price'],
                    'sell_retail_price' => $variant['sell_retail_price'] ?? null,
                    'code' => 'V-' . rand(10000000, 99999999)
                ]);

                ProductStock::create([
                    'store_id' => $store->id,
                    'user_id' => $admin->id,
                    'supplier_id' => $supplier->id,
                    'product_id' => $obj->id,
                    'variant_id' => $item->id,
                    'code' => fake()->numerify('STV-##############'),
                    'amount_added' => 100,
                    'amount_available' => 100,
                    'expired_at' => fake()->randomElement([null, now()->addDays(rand(5, 14))]),
                ]);
            }
        }

        $product_ingredients = [
            [
                'name' => 'Mie Goreng',
                'image' => "https://d1sag4ddilekf6.azureedge.net/compressed_webp/items/IDITE2021011613585678794/detail/menueditor_item_a7de220c535a4d848069cc73eb8c4296_1610805511918514698.webp",
                'category_id' => 1,
                'store_id' => 1,
                'buy_price' => 13000,
                'sell_price' => 17000,
                'ingredients' =>
                [
                    [
                        'ingredient' => 'Gula Pasir',
                        'amount' => 30
                    ],
                    [
                        'ingredient' => 'Telur Ayam',
                        'amount' => 1
                    ]
                ]
            ],
            [
                'name' => 'Rice Bowl',
                'image' => "https://d1vbn70lmn1nqe.cloudfront.net/prod/wp-content/uploads/2023/07/14082947/Ini-Resep-Rice-Bowl-Sehat-dan-Padat-Nutrisi-untuk-Bekal-.jpg.webp",
                'category_id' => 1,
                'store_id' => 1,
                'buy_price' => 10000,
                'sell_price' => 15000,
                'ingredients' => [
                    [
                        'ingredient' => 'Telur Ayam',
                        'amount' => 1
                    ],
                    [
                        'ingredient' => 'Masako',
                        'amount' => 1
                    ]
                ]
            ],
        ];

        foreach ($product_ingredients as $product) {
            $obj = Product::create([
                'store_id' => 1,
                'name' => $product['name'],
                'image' => $product['image'],
                'category_id' => $product['category_id'],
                'store_id' => $product['store_id'],
                'code' => 'P-' . rand(10000000, 99999999), // 'P-########
                'buy_price' => $product['buy_price'],
                'sell_price' => $product['sell_price'],
            ]);

            foreach ($product['ingredients'] as $item) {
                $ingredient = ProductIngredient::where('name', $item['ingredient'])->first();

                ProductIngredientRelation::create([
                    'product_id' => $obj->id,
                    'ingredient_id' => $ingredient->id,
                    'amount' => $item['amount'],
                ]);
            }
        }
    }
}
