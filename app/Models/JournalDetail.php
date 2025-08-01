<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'journal_id',
        'store_id',
        'account_code',
        'debit',
        'credit',
    ];
}
