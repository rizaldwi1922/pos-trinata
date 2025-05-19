<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseReturn extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'supplier_id',
        'total_amount',
        'return_date',
        'notes',
        'journal_id'
    ];

    public function supplier(): HasOne{
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }
}
