<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ingredient_stocks', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->integer('item_price')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_stocks', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'item_price']);
        });
    }
};
