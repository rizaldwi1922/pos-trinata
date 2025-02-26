<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = str()->slug($value);
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
