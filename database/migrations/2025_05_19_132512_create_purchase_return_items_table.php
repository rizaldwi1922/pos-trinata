<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('purchase_return_id');
            $table->unsignedBigInteger('product_variant_id');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns');
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
            $table->foreign('store_id')->references('id')->on('stores');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};
