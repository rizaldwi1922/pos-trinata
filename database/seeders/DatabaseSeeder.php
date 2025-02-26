<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\News;
use App\Models\Shift;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $super =  User::create([
            'name' => 'Super Admin',
            'email' => 'super@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123')
        ]);

        $webmin =  User::create([
            'name' => 'Admin',
            'email' => 'webmin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123')
        ]);

        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);

        $super->syncRoles(['super-admin']);
        $webmin->syncRoles(['web-admin']);

        if (env('DB_SEED_DEMO')) {
            $store = Store::create([
                'name' => 'Demo POS',
                'address' => 'Jl. Demo No. 1, Demo, Demo',
                'image' => fake()->imageUrl(200, 200),
                'code' => fake()->numerify('S-#######'),
            ]);
            $store->createDefaultStoreSetting();

            $admin = User::create([
                'name' => 'Admin Demo Store',
                'email' => 'admin@gmail.com',
                'store_id' => $store->id,
                'email_verified_at' => now(),
                'password' => Hash::make('admin123')
            ]);

            $user = User::create([
                'name' => 'Demo Store',
                'email' => 'demo@gmail.com',
                'store_id' => $store->id,
                'email_verified_at' => now(),
                'password' => Hash::make('admin123')
            ]);

            $user->syncRoles(['user']);
            $admin->syncRoles(['admin']);

            $supplier = Supplier::create([
                'store_id' => $store->id,
                'name' => 'Supplier',
                'phone' => '081234567890',
                'email' => 'supplier@gmail.com',
                'address' => 'Jl. Jakarta',
            ]);

            User::factory(15)->create([
                'store_id' => $store->id,
            ]);

            Customer::factory(15)->create([
                'store_id' => $store->id,
            ]);

            News::factory(10)->create();

            $users = User::withoutRole(['super-admin', 'web-admin', 'admin'])->get();
            $users->each(function ($user) {
                $user->assignRole(['user']);
            });

            $this->call(CategorySeeder::class);
            $this->call(ProductUnitSeeder::class);
            $this->call(IngredientSeeder::class);
            $this->call(ProductSeeder::class);
            $this->call(ProductIngredientSeeder::class);
            $this->call(PaymentMethodSeeder::class);

            $shift = Shift::create([
                'store_id' => $store->id,
                'user_id' => $admin->id,
                'start_shift_at' => now(),
                'amount_start' => 0,
            ]);

            // Transaction::factory(15)->create([
            //     'store_id' => $store->id,
            //     'shift_id' => $shift->id,
            // ]);
        }
    }
}
