<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;
    protected $table = 'journal_entrys';
    protected $fillable = [
        'store_id',
        'related_table',
        'description',
        'entry_date',
    ];
}
