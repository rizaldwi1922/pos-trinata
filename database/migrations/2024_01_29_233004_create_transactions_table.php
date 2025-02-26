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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_id')->unsigned();
            $table->bigInteger('shift_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->bigInteger('payment_method_id')->unsigned();
            $table->string('trx_id');
            $table->integer('status')->default(1); // 1: success, 0: pending
            $table->integer('type')->default(1); // 1: sale, 2: pre-order
            $table->integer('amount_received');
            $table->integer('amount_less')->default(0);
            $table->integer('amount_total');
            $table->integer('amount_profit')->default(0);
            $table->integer('amount_discount')->default(0);
            $table->integer('amount_retur')->default(0);
            $table->integer('amount_deposit')->default(0);
            $table->integer('total_items');
            $table->text('data');
            $table->timestamp('repaid_at')->nullable();

            // foreign key
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
