<?php

namespace Database\Seeders;

use App\Models\WebPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin_permissions = [
            'dashboard' => ['view'],
            'transactions' => ['view', 'delete'],
            'shifts' => ['view'],
            'report_transactions' => ['view'],
            'report_purchases' => ['view'],
            'products' => ['create', 'view', 'update', 'delete'],
            'product_barcodes' => ['view'],
            'product_variants' => ['create', 'view', 'update', 'delete'],
            'product_ingredients' => ['create', 'view', 'update', 'delete'],
            'product_categories' => ['create', 'view', 'update', 'delete'],
            'product_ingredient_stocks' => ['create', 'view', 'update', 'delete'],
            'product_variant_stocks' => ['create', 'view', 'update', 'delete'],
            'ingredient_stocks' => ['create', 'view', 'update', 'delete'],
            'product_units' => ['create', 'view', 'update', 'delete'],
            'purchases' => ['create', 'view', 'update', 'delete'],
            'ingredients' => ['create', 'view', 'update', 'delete'],
            'purchase_payments' => ['create', 'view', 'update', 'delete'],
            'daily_stocks' => ['create', 'view', 'update'],
            'users' => ['create', 'view', 'update', 'delete'],
            'customers' => ['create', 'view', 'update', 'delete'],
            'suppliers' => ['create', 'view', 'update', 'delete'],
            'payment-methods' => ['create', 'view', 'update', 'delete'],
            'expenses' => ['create', 'view', 'update', 'delete'],
        ];

        $webmin_permissions = [
            'dashboard' => ['view'],
            'news' => ['create', 'view', 'update', 'delete'],
            'register-user' => ['view'],
        ];

        // create admin permission
        foreach ($admin_permissions as $name => $permissions) {
            foreach ($permissions as $permission) {
                $key = 'can_' . $permission . '_admin_' . $name;
                $key = str_replace('-', '_', $key);
                Permission::create(['name' => $key]);
            }
        }

        // create webmin permission
        foreach ($webmin_permissions as $name => $permissions) {
            foreach ($permissions as $permission) {
                $key = 'can_' . $permission . '_webmin_' . $name;
                $key = str_replace('-', '_', $key);
                Permission::create(['name' => $key]);
            }
        }

        // create POS permission
        Permission::create(['name' => 'can_access_pos']);

        // files with permission
        $admin_files = [
            'admin.dashboard.index' => 'can_view_admin_dashboard',
            'admin.transactions.index' => 'can_view_admin_transactions',
            'admin.transactions.destroy' => 'can_delete_admin_transactions',
            'admin.shifts.index' => 'can_view_admin_shifts',
            'admin.shifts.show' => 'can_view_admin_shifts',
            'admin.reports.transactions.index' => 'can_view_admin_report_transactions',
            'admin.reports.transactions.show' => 'can_view_admin_report_transactions',
            'admin.reports.transactions.download' => 'can_view_admin_report_transactions',
            'admin.reports.purchases.index' => 'can_view_admin_report_purchases',
            'admin.reports.purchases.show' => 'can_view_admin_report_purchases',
            'admin.reports.purchases.download' => 'can_view_admin_report_purchases',
            'admin.products.index' => 'can_view_admin_products',
            'admin.products.form_1' => 'can_create_admin_products',
            'admin.products.form_2' => 'can_update_admin_products',
            'admin.products.barcode' => 'can_view_admin_product_barcodes',
            'admin.purchases.index' => 'can_view_admin_purchases',
            'admin.purchases.form_1' => 'can_create_admin_purchases',
            'admin.purchases.form_2' => 'can_update_admin_purchases',
            'admin.purchases.show' => 'can_view_admin_purchases',
            'admin.expenses.index' => 'can_view_admin_expenses',
            'admin.expenses.form_1' => 'can_create_admin_expenses',
            'admin.expenses.form_2' => 'can_update_admin_expenses',
            'admin.expenses.show' => 'can_view_admin_expenses',
            'admin.purchase-payments.index' => 'can_view_admin_purchase_payments',
            'admin.purchase-payments.form_1' => 'can_create_admin_purchase_payments',
            'admin.purchase-payments.form_2' => 'can_update_admin_purchase_payments',
            'admin.product-variants.index' => 'can_view_admin_product_variants',
            'admin.product-variants.form_1' => 'can_create_admin_product_variants',
            'admin.product-variants.form_2' => 'can_update_admin_product_variants',
            'admin.daily-stocks.index' => 'can_view_admin_daily_stocks',
            'admin.daily-stocks.form_1' => 'can_create_admin_daily_stocks',
            'admin.daily-stocks.form_2' => 'can_update_admin_daily_stocks',
            'admin.product-ingredients.index' => 'can_view_admin_product_ingredients',
            'admin.product-ingredients.form_1' => 'can_create_admin_product_ingredients',
            'admin.product-ingredients.form_2' => 'can_update_admin_product_ingredients',
            'admin.product-ingredient-stocks.index' => 'can_view_admin_product_ingredient_stocks',
            'admin.product-ingredient-stocks.form_1' => 'can_create_admin_product_ingredient_stocks',
            'admin.product-ingredient-stocks.form_2' => 'can_update_admin_product_ingredient_stocks',
            'admin.product-variant-stocks.index' => 'can_view_admin_product_variant_stocks',
            'admin.product-variant-stocks.form_1' => 'can_create_admin_product_variant_stocks',
            'admin.product-variant-stocks.form_2' => 'can_update_admin_product_variant_stocks',
            'admin.product-categories.index' => 'can_view_admin_product_categories',
            'admin.product-categories.form_1' => 'can_create_admin_product_categories',
            'admin.product-categories.form_2' => 'can_update_admin_product_categories',
            'admin.ingredients.index' => 'can_view_admin_ingredients',
            'admin.ingredients.show' => 'can_view_admin_ingredients',
            'admin.ingredients.form_1' => 'can_create_admin_ingredients',
            'admin.ingredients.form_2' => 'can_update_admin_ingredients',
            'admin.ingredient-stocks.index' => 'can_view_admin_ingredient_stocks',
            'admin.ingredient-stocks.form_1' => 'can_create_admin_ingredient_stocks',
            'admin.ingredient-stocks.form_2' => 'can_update_admin_ingredient_stocks',
            'admin.product-units.index' => 'can_view_admin_product_units',
            'admin.product-units.form_1' => 'can_create_admin_product_units',
            'admin.product-units.form_2' => 'can_update_admin_product_units',
            'admin.users.index' => 'can_view_admin_users',
            'admin.users.form_1' => 'can_create_admin_users',
            'admin.users.form_2' => 'can_update_admin_users',
            'admin.customers.index' => 'can_view_admin_customers',
            'admin.customers.form_1' => 'can_create_admin_customers',
            'admin.customers.form_2' => 'can_update_admin_customers',
            'admin.suppliers.index' => 'can_view_admin_suppliers',
            'admin.suppliers.form_1' => 'can_create_admin_suppliers',
            'admin.suppliers.form_2' => 'can_update_admin_suppliers',
            'admin.payment-methods.index' => 'can_view_admin_payment_methods',
            'admin.payment-methods.form_1' => 'can_create_admin_payment_methods',
            'admin.payment-methods.form_2' => 'can_update_admin_payment_methods',
        ];

        $webmin_files = [
            'web.admin.dashboard.index' => 'can_view_webmin_dashboard',
            'web.admin.register-user.index' => 'can_view_webmin_register_user',
            'web.admin.news.index' => 'can_view_webmin_news',
            'web.admin.news.form' => 'can_create_webmin_news',
            'web.admin.news.form' => 'can_update_webmin_news',
        ];

        // create web page
        self::createWebPage($admin_files);
        self::createWebPage($webmin_files);

        // default permission
        $roleAdmin = Role::where('name', 'admin')->first();
        $permissions = Permission::where('name', env('DB_SEARCH_OPERATOR'), '%_admin_%')->pluck('name', 'id');
        $roleAdmin->syncPermissions(array_merge($permissions->toArray(), ['can_access_pos']));

        $roleWebmin = Role::where('name', 'web-admin')->first();
        $permissions = Permission::where('name', env('DB_SEARCH_OPERATOR'), '%_webmin_%')->pluck('name', 'id');
        $roleWebmin->syncPermissions($permissions);

        $roleUser = Role::where('name', 'user')->first();
        $roleUser->syncPermissions([
            'can_view_admin_dashboard',
            'can_access_pos',
        ]);
    }

    function createWebPage($files)
    {
        foreach ($files as $file => $permission) {
            $file = str_replace(['_1', '_2'], '', $file);
            WebPage::create([
                'file_path' => $file,
                'permission' => $permission,
            ]);
        }
    }
}
