<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['modal'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = str()->slug($value);
    }

    public function getModalAttribute()
    {
        $productId = $this->id;
        $result = DB::table(DB::raw('(SELECT 
                    pir.product_id,
                    pir.ingredient_id,
                    pir.amount,
                    (CASE
                        WHEN price_stk.unit_price > 0 THEN price_stk.unit_price
                        ELSE pi.price
                    END) as base_price
                FROM product_ingredient_relations as pir
                INNER JOIN product_ingredients as pi on pir.ingredient_id = pi.id
                LEFT JOIN (
                    SELECT 
                        main.ingredient_id,
                        main.unit_price,
                        main.item_price,
                        main.amount_available,
                        main.amount_added
                    FROM ingredient_stocks AS main
                    JOIN (
                        SELECT ingredient_id, MIN(created_at) AS first_created
                        FROM ingredient_stocks
                        WHERE (expired_at IS NULL OR expired_at > NOW()) 
                        AND amount_available > 0
                        GROUP BY ingredient_id
                    ) AS sub ON main.ingredient_id = sub.ingredient_id AND main.created_at = sub.first_created
                    WHERE (main.expired_at IS NULL OR main.expired_at > NOW()) 
                    AND main.amount_available > 0
                ) as price_stk on pi.id = price_stk.ingredient_id
            ) as stk'))
            ->where('product_id', $productId)
            ->selectRaw('product_id, SUM(amount * base_price) as total_modal')
            ->groupBy('product_id')
            ->first();

        return $result ? $result->total_modal : 0;
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(ProductIngredient::class, 'product_ingredient_relations', 'product_id', 'ingredient_id');
    }

    public static function getAvailableProductIngredientStock($productId)
    {
        // Ambil ingredient yang dibutuhkan beserta jumlah yang diperlukan untuk produk tertentu
        $ingredientRelations = ProductIngredientRelation::where('product_id', $productId)->get()->map(function ($item) {
            $obj = new \stdClass();
            $obj->id = $item->ingredient_id;
            $obj->amount = $item->amount;
            return $obj;
        });

        // Ambil stok yang tersedia untuk setiap ingredient yang dibutuhkan
        $availableIngredientStocks = DB::table('ingredient_stocks')
            ->whereIn('ingredient_id', $ingredientRelations->pluck('id'))
            ->select('ingredient_id', DB::raw('sum(amount_available) as total'))
            ->where('amount_available', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->groupBy('ingredient_id')
            ->get()
            ->keyBy('ingredient_id'); // Key by ingredient_id for easier lookup

        // Hitung jumlah maksimum produk yang bisa dibuat
        $maxProductsAvailable = $ingredientRelations->map(function ($ingredient) use ($availableIngredientStocks) {
            // Cek apakah stok tersedia untuk ingredient tersebut
            if (isset($availableIngredientStocks[$ingredient->id])) {
                $availableAmount = $availableIngredientStocks[$ingredient->id]->total;
                // Hitung kemungkinan berapa kali ingredient ini cukup untuk jumlah yang dibutuhkan
                return intdiv($availableAmount, $ingredient->amount);
            }
            return 0; // Jika tidak ada stok, maka 0
        })->min(); // Ambil minimum dari semua ingredient, karena semua harus ada

        return $maxProductsAvailable;
    }
}
