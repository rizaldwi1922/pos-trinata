<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function createDefaultStoreSetting()
    {
        $settings = [
            [
                'store_id' => $this->id,
                'key' => 'receipt_logo',
                'value' => '0',
            ],
            [
                'store_id' => $this->id,
                'key' => 'receipt_logo_size',
                'value' => '70',
            ],
            [
                'store_id' => $this->id,
                'key' => 'alert_stock_minimum',
                'value' => '10',
            ]
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
