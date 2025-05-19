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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('total_amount', 15, 2);
            $table->date('return_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('journal_id');
            $table->foreign('journal_id')->references('id')->on('journal_entrys');
            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
