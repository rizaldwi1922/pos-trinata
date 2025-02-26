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
        Schema::create('ingredient_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_id')->unsigned();
            $table->bigInteger('ingredient_id')->unsigned();
            $table->bigInteger('supplier_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('purchase_id')->unsigned()->nullable();
            $table->string('code')->nullable();
            $table->integer('amount_added');
            $table->integer('amount_available');
            $table->timestamp('expired_at')->nullable();

            // foreign key
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('ingredient_id')->references('id')->on('product_ingredients')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_stocks');
    }
};
