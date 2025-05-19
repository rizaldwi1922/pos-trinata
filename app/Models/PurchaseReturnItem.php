<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
       'purchase_return_id',
       'product_variant_id',
       'quantity',
       'price',
    ];
}
