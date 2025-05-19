<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'transaction_id',
        'customer_id',
        'total_due',
        'total_paid',
        'due_date',
        'status',
    ];
}
