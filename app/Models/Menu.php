<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function getAdminMenus()
    {
        return [
            [
                'name' => 'Dashboard',
                'permission' => 'dashboard',
                'icon' => 'bx bx-home-circle',
                'route' => route('admin.dashboard'),
            ],
            [
                'name' => 'Point Of Sales',
                'icon' => 'bx bx-calculator',
                'route' => route('app.point_of_sale'),
            ],
            [
                'label' => 'Operasional',
                'separator' => true,
            ],
            [
                'name' => 'Riwayat Transaksi',
                'permission' => 'transactions',
                'icon' => 'bx bx-receipt',
                'route' => route('admin.transactions.index'),
            ],
            [
                'name' => 'Riwayat Shift',
                'permission' => 'shifts',
                'icon' => 'bx bx-time-five',
                'route' => route('admin.shifts.index'),
            ],
            [
                'name' => 'Pembeliaan',
                'permission' => 'purchases',
                'icon' => 'bx bx-box',
                'route' => route('admin.purchases.index'),
            ],
            [
                'name' => 'Pengeluaran',
                'permission' => 'expenses',
                'icon' => 'bx bx-money',
                'route' => route('admin.expenses.index'),
            ],
            [
                'name' => 'Persediaan',
                'icon' => 'bx bx-grid-alt',
                'route' => 'javascript:void(0)',
                'child' => [
                    [
                        'name' => 'Stok Produk Varian',
                        'permission' => 'product_variant_stocks',
                        'route' => route('admin.products.variant-stocks.index'),
                    ],
                    [
                        'name' => 'Stok Produk Bahan',
                        'permission' => 'product_ingredient_stocks',
                        'route' => route('admin.products.ingredient-stocks.index'),
                    ],
                    [
                        'name' => 'Stok Bahan',
                        'permission' => 'ingredient_stocks',
                        'route' => route('admin.ingredients-stocks.index'),
                    ],
                    [
                        'name' => 'Stok Opname',
                        'permission' => 'daily_stocks',
                        'route' => route('admin.daily-stocks.index'),
                    ]
                ],
            ],
            [
                'name' => 'Laporan',
                'icon' => 'bx bx-line-chart',
                'route' => 'javascript:void(0)',
                'child' => [
                    [
                        'name' => 'Laporan Penjualan',
                        'permission' => 'report_transactions',
                        'route' => route('admin.reports.transactions.index'),
                    ],
                    [
                        'name' => 'Laporan Pembelian',
                        'permission' => 'report_purchases',
                        'route' => route('admin.reports.purchases.index'),
                    ],
                ],
            ],
            [
                'label' => 'Master Data',
                'separator' => true,
            ],
            [
                'name' => 'Produk',
                'icon' => 'bx bx-book-open',
                'route' => 'javascript:void(0)',
                'child' => [
                    [
                        'name' => 'Daftar Produk',
                        'permission' => 'products',
                        'route' => route('admin.products.index'),
                    ],
                    [
                        'name' => 'Produk Varian',
                        'permission' => 'product_variants',
                        'route' => route('admin.products.variants.index'),
                    ],
                    [
                        'name' => 'Produk Bahan',
                        'permission' => 'product_ingredients',
                        'route' => route('admin.products.ingredients.index'),
                    ],
                    [
                        'name' => 'Kategori Produk',
                        'permission' => 'product_categories',
                        'route' => route('admin.products.categories.index'),
                    ],
                    [
                        'name' => 'Unit Produk',
                        'permission' => 'product_units',
                        'route' => route('admin.products.units.index'),
                    ],
                ],
            ],
            [
                'name' => 'Data Bahan',
                'icon' => 'bx bxs-package',
                'permission' => 'ingredients',
                'route' => route('admin.ingredients.index'),
            ],
            [
                'name' => 'Data Karyawan',
                'permission' => 'users',
                'icon' => 'bx bx-user-pin',
                'route' => route('admin.users.index'),
            ],
            [
                'name' => 'Data Pelanggan',
                'permission' => 'customers',
                'icon' => 'bx bxs-user-rectangle',
                'route' => route('admin.customers.index'),
            ],
            [
                'name' => 'Data Supplier',
                'permission' => 'suppliers',
                'icon' => 'bx bx-box',
                'route' => route('admin.suppliers.index'),
            ],
            [
                'name' => 'Metode Pembayaran',
                'permission' => 'payment-methods',
                'icon' => 'bx bx-credit-card',
                'route' => route('admin.payment-methods.index'),
            ],
            [
                'label' => 'Lainnya',
                'separator' => true,
            ],
            [
                'name' => 'Pengaturan',
                'icon' => 'bx bx-cog',
                'route' => route('admin.settings.index'),
            ],
            [
                'name' => 'Keluar',
                'icon' => 'bx bx-exit',
                'route' => route('auth.logout'),
            ]
        ];
    }

    public static function getSuperAdminMenus()
    {
        return [
            [
                'name' => 'Toko',
                'icon' => 'bx bx-store',
                'route' => route('super.stores.index'),
            ],
            [
                'label' => 'Master Data',
                'separator' => true,
            ],
            [
                'name' => 'Role Pengguna',
                'icon' => 'bx bx-user-pin',
                'route' => route('super.roles.index'),
            ],
            [
                'name' => 'Hak Akses',
                'icon' => 'bx bxs-check-shield',
                'route' => route('super.permissions.index'),
            ],
            [
                'label' => 'Lainnya',
                'separator' => true,
            ],
            [
                'name' => 'Keluar',
                'icon' => 'bx bx-exit',
                'route' => route('auth.logout'),
            ]
        ];
    }

    public static function getWebAdminMenus()
    {
        return
            [
                [
                    'name' => 'Dashboard',
                    'permission' => 'dashboard',
                    'icon' => 'bx bx-home-circle',
                    'route' => route('web.admin.dashboard'),
                ],
                [
                    'name' => 'Pendafar',
                    'permission' => 'register-user',
                    'icon' => 'bx bx-user-pin',
                    'route' => route('web.admin.register-user'),
                ],
                [
                    'name' => 'Blog',
                    'icon' => 'bx bx-book-open',
                    'route' => route('web.admin.news.index'),
                ],
                [
                    'label' => 'Lainnya',
                    'separator' => true,
                ],
                [
                    'name' => 'Keluar',
                    'icon' => 'bx bx-exit',
                    'route' => route('auth.logout'),
                ]
            ];
    }
}
