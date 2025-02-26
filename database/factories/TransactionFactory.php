<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount_less = $this->faker->numberBetween(1000, 100000);
        $amount_received = $this->faker->numberBetween($amount_less, 100000);
        $amount_total = $amount_received + $amount_less;

        return [
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'payment_method_id' => PaymentMethod::inRandomOrder()->first()->id,
            'trx_id' => 'TRX-' . $this->faker->unique()->numberBetween(10000000, 99999999),
            'amount_received' => $amount_received,
            'amount_less' => $amount_less,
            'amount_total' => $amount_total,
            'total_items' => 3,
            'data' => [
                [
                    'product_id' => 1,
                    'variant_id' => 1,
                    'name' => 'Product 1',
                    'buy_price' => 1000,
                    'sell_price' => 2000,
                    'amount' => 1,
                    'amount_retur' => 0,
                ],
                [
                    'product_id' => 2,
                    'variant_id' => 2,
                    'name' => 'Product 2',
                    'buy_price' => 2000,
                    'sell_price' => 3000,
                    'amount' => 2,
                    'amount_retur' => 0,
                ],
                [
                    'product_id' => 3,
                    'variant_id' => 3,
                    'name' => 'Product 3',
                    'buy_price' => 3000,
                    'sell_price' => 4000,
                    'amount' => 3,
                    'amount_retur' => 0,
                ]
            ]
        ];
    }
}
